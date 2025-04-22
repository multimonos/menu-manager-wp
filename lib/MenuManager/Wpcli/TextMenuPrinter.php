<?php

namespace MenuManager\Wpcli;

use MenuManager\Vendor\Kalnoy\Nestedset\Collection;

class TextMenuPrinter {

    public function print( Collection $tree ): void {
        echo "\n" . str_pad( 'ID', 8 )
            . str_pad( 'Sort', 8 )
            . str_pad( 'Type', 15 )
            . str_pad( 'De', 5 )
            . 'Description';

        $this->traverse( $tree );
    }

    protected function traverse( $nodes, $prefix = '' ) {

        foreach ( $nodes as $node ) {
            echo "\n"
                . str_pad( $node->id, 8 )
                . str_pad( $node->sort_order, 8 )
                . str_pad( $node->type->value, 15 )
                . str_pad( $node->depth, 5 )
                . $prefix
                . ' ' . $node->title
                . ' ↓[' . $node->sort_order . ']'
                . ($node->meta->prices ? '  $[' . $node->meta->prices . ']' : '');

            if ( $node->meta->tags ) {
                echo '  #[' . $node->meta->tags . ']';
            }

            if ( $node->description ) {
                echo '  §[' . substr( $node->description, 0, 20 ) . '...]';
            }

            $this->traverse( $node->children, $prefix . '..' );
        }
    }
}