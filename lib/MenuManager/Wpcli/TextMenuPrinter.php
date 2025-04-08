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
            echo "\n"
                . $prefix
                . ' ' . $node->title
                . ($node->menuItem->prices ? '  $[' . $node->menuItem->prices . ']' : '');

            $this->traverse( $node->children, $prefix . '..' );
        }
    }
}