<?php

namespace MenuManager\Tasks\Menu;

use MenuManager\Model\Menu;
use MenuManager\Model\Node;
use MenuManager\Service\Database;
use MenuManager\Tasks\TaskResult;

class CloneMenuTask {
    public function run( mixed $src_id_or_slug, string $target_slug ): TaskResult {
        Database::load()::connection()->enableQueryLog();

        // Validate.

        // guard : target is not numeric
        if ( is_numeric( $target_slug ) ) {
            return TaskResult::failure( "Target menu '{$target_slug} must be a sluglike string." );
        }

        // guard : src == target
        if ( $src_id_or_slug === $target_slug ) {
            return TaskResult::failure( "Source and target menu cannot be the same." );
        }

        // guard : target menu exists
        $dst = Menu::find( $target_slug );

        if ( $dst instanceof Menu ) {
            return TaskResult::failure( "Target menu '{$target_slug}' already exists." );
        }

        // guard : src menu must exist
        $src = Menu::find( $src_id_or_slug );

        if ( $src === null ) {
            return TaskResult::failure( "Source menu '{$src_id_or_slug}' not found." );
        }

        // guard : root node must xist
        $root = Node::findRootNode( $src );
        if ( $root === null ) {
            return TaskResult::failure( "Root node for menu '{$src->post->post_name}' not found." );
        }

        // create dst
        $dst = Menu::create( ['post_title' => $target_slug, 'post_name' => $target_slug] );
        if ( $dst === null ) {
            return TaskResult::failure( "Failed to create target ment '{$target_slug}'." );
        }

        try {
            Database::load()->getConnection()->transaction( function () use ( $root, $dst ) {
                $this->cloneNode( $dst, $root );
            } );

        } catch (\Throwable $e) {
            // cleanup
            Menu::deleteByPostId( $dst->ID );
            return TaskResult::failure( "Clone failed.  " . $e->getMessage() );
        }


        // Ok.
        $queries = Database::load()::connection()->getQueryLog();
        return TaskResult::success( "Cloned menu '{$src_id_or_slug}' -> '{$target_slug}'.", [
            count( $queries ) . " queries",
        ] );
    }

    protected function cloneNode( Menu $menu, Node $node, Node $parent = null ) {
        // Node
        $newNode = $node->replicate( ['id', '_lft', '_rgt', 'parent_id', 'depth'] );
        $newNode->menu_id = $menu->post->ID;

        if ( is_null( $parent ) ) { // root node
            $newNode->save();
            $newNode->refresh();
            $newNode->fixTree();
        } else {
            $newNode = $parent->children()->create( $newNode->toArray() );

            // NodeMeta
            if ( $node->meta->exists ) {
                $newMeta = $node->meta->replicate( ['id'] );
                $newMeta->node_id = $newNode->id;
                $newMeta->save();
            }
        }

        foreach ( $node->children as $child ) {
            $this->cloneNode( $menu, $child, $newNode );
        }
    }

}