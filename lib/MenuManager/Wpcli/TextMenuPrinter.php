<?php

namespace MenuManager\Wpcli;

class TextMenuPrinter {

    public function print( \Kalnoy\Nestedset\Collection $tree ): void {
        $this->traverse( $tree );
    }

    protected function traverse( $nodes, $prefix = '' ) {
        foreach ( $nodes as $node ) {
            echo "\n"
                . $node->depth . ' '
                . $prefix
                . ' ' . $node->title
                . ($node->menuItem->prices ? '  $[' . $node->menuItem->prices . ']' : '');

            $this->traverse( $node->children, $prefix . '..' );
        }
    }
}