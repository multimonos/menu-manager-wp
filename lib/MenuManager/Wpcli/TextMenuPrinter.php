<?php

namespace MenuManager\Wpcli;

class TextMenuPrinter {

    public function print( \Kalnoy\Nestedset\Collection $tree ): void {
        $this->traverse( $tree );
    }

    protected function traverse( $nodes, $prefix = '' ) {
        foreach ( $nodes as $node ) {
            echo "\n"
                . str_pad( $node->type, 15 )
                . str_pad( $node->depth, 3 )
                . $prefix
                . ' ' . $node->title
                . ($node->menuItem->prices ? '  $[' . $node->menuItem->prices . ']' : '');

            if ( $node->menuItem->tags ) {
                echo '  @[' . $node->menuItem->tags . ']';
            }

            if ( $node->description ) {
                echo '  §[' . substr( $node->description, 0, 20 ) . '...]';
            }

            $this->traverse( $node->children, $prefix . '..' );
        }
    }
}