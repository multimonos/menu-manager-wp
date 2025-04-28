<?php

namespace MenuManager\Task;

use MenuManager\Model\MenuPost;
use MenuManager\Model\Node;
use MenuManager\Service\Database;
use MenuManager\Vendor\Kalnoy\Nestedset\Collection;

class ViewMenuAsTextTask {
    public function run( int|string $menu_id, string $pagename = null ): TaskResult {

        $menu = MenuPost::find( $menu_id );

        if ( $menu === null ) {
            return TaskResult::failure( "Menu not found '{$menu_id}'." );
        }

        Database::load()::connection()->enableQueryLog();

        // TREE
        $root = empty( $page )
            ? Node::findRootNode( $menu )
            : Node::findPageNode( $menu, $page );

        $tree = Node::getSortedMenu( $menu, $root );

        if ( ! $tree || $tree->count() === 0 ) {
            return TaskResult::failure( "Menu tree not found or is empty '" . trim( $menu->post->ID . ' ' . $pagename ) . "'." );
        }

        // print
        echo "\nMenu={$menu->post->post_name}" . (! empty( $pagename ) ? '/' . $pagename : '');

        $this->print( $tree );

        $queries = Database::load()::connection()->getQueryLog();

        return TaskResult::success( count( $queries ) . ' queries' );
    }

    protected function print( Collection $tree ): void {

        echo "\n";

        echo "\n" . str_pad( 'ID', 8 )
            . str_pad( 'Sort', 8 )
            . str_pad( 'Type', 15 )
            . str_pad( 'De', 5 )
            . 'Description';

        $this->visit( $tree );

        echo "\n\n";
    }

    protected function visit( $nodes, $prefix = '' ) {

        foreach ( $nodes as $node ) {
            echo "\n"
                . str_pad( $node->id, 8 )
                . str_pad( $node->sort_order, 8 )
                . str_pad( $node->type->value, 15 )
                . str_pad( $node->depth, 5 )
                . $prefix
                . ' ' . $node->title
                . '  ↓[' . $node->sort_order . ']'
                . ($node->meta->prices ? '  $[' . $node->meta->prices . ']' : '');

            if ( $node->meta->tags ) {
                echo '  #[' . $node->meta->tags . ']';
            }

            if ( $node->description ) {
                echo '  §[' . substr( $node->description, 0, 20 ) . '...]';
            }

            $this->visit( $node->children, $prefix . '..' );
        }
    }
}