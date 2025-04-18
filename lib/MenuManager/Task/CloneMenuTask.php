<?php

namespace MenuManager\Task;

use MenuManager\Database\db;
use MenuManager\Database\Model\Node;
use MenuManager\Database\PostType\MenuPost;

class CloneMenuTask {
    public function run( mixed $src_id_or_slug, string $target_slug ): TaskResult {
        db::load()::connection()->enableQueryLog();

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
        $dst = MenuPost::find( $target_slug );

        if ( $dst instanceof \WP_Post ) {
            return TaskResult::failure( "Target menu '${target_slug}' already exists." );
        }

        // guard : src menu must exist
        $src = MenuPost::find( $src_id_or_slug );

        if ( ! $src instanceof \WP_Post ) {
            return TaskResult::failure( "Source menu '{$src_id_or_slug}' not found." );
        }

        // guard : root node must xist
        $root = Node::findRootNode( $src );
        if ( $root === null ) {
            return TaskResult::failure( "Root node for menu '{$src->post_name}' not found." );
        }

        // create dst
        $dst = MenuPost::create( ['post_title' => $target_slug, 'post_name' => $target_slug] );
        if ( ! $dst instanceof \WP_Post ) {
            return TaskResult::failure( "Failed to create target ment '{$target_slug}'." );
        }

        try {
            db::load()->getConnection()->transaction( function () use ( $root, $dst ) {
                $this->cloneNode( $dst, $root );
            } );

        } catch (\Throwable $e) {
            // delete the dst
            MenuPost::delete( $dst->ID );
            return TaskResult::failure( "Clone failed.  " . $e->getMessage() );
        }


        // Ok.
        $queries = db::load()::connection()->getQueryLog();
        return TaskResult::success( "Cloned menu '{$src_id_or_slug}' -> '{$target_slug}'.", [
            count( $queries ) . " queries",
        ] );
    }

    protected function cloneNode( \WP_Post $menu, Node $node, Node $parent = null ) {
        // Node
        $newNode = $node->replicate( ['id', '_lft', '_rgt', 'parent_id', 'depth'] );
        $newNode->menu_id = $menu->ID;
        $newNode->title = $menu->post_name . '--' . $node->title;

        echo " " . $node->id;

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