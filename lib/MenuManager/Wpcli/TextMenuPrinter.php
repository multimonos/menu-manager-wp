<?php

namespace MenuManager\Wpcli;

use MenuManager\Database\Model\MenuNode;

class TextMenuPrinter {

    public function print( \WP_Post $menu ): void {

        $root = MenuNode::where( 'menu_id', $menu->ID )->where( 'type', 'root' )->first();

        $nodes = MenuNode::descendantsOf( $root->id );

        $tree = $nodes->toTree();

        echo "\n{$menu->post_name}";

        $this->traverse( $tree );
    }

    protected function traverse( $nodes, $prefix = '' ) {
        foreach ( $nodes as $node ) {
            echo "\n" . $prefix . ' ' . $node->title;

            $this->traverse( $node->children, $prefix . '..' );

            $node->menuItems()->each( function ( $item ) use ( $prefix ) {
                echo "\n{$prefix} {$prefix} " . $item->title;
            } );
        }
    }
}