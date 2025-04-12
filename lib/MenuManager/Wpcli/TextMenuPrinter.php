<?php

namespace MenuManager\Wpcli;

use MenuManager\Vendor\Kalnoy\Nestedset\Collection;

class TextMenuPrinter {

    public function print( Collection $tree ): void {
        $this->traverse( $tree );
    }

    protected function traverse( $nodes, $prefix = '' ) {
        foreach ( $nodes as $node ) {
            echo "\n"
                . str_pad( $node->type, 15 )
                . str_pad( $node->depth, 3 )
                . $prefix
                . ' ' . $node->title
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