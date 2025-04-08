<?php

namespace MenuManager\Wpcli\Commands;

use League\Csv\Writer;
use MenuManager\Database\db;
use MenuManager\Database\Model\MenuNode;
use MenuManager\Database\PostType\MenuPost;
use WP_CLI;

class RootCommands {
    /**
     * Export menu to CSV.
     *
     * ## OPTIONS
     *
     * <menu_id>
     * : ID of the menu.
     *
     * [<csv_file>]
     * : The CSV file to write.
     *
     * ## EXAMPLES
     *
     *    wp mm export 666 export.csv
     *
     * @when after_wp_load
     */
    public function export( $args, $assoc_args ) {

        // menu
        $menu_id = $args[0];

        $menu = MenuPost::find( $menu_id );


        if ( ! $menu instanceof \WP_Post ) {
            WP_CLI::error( "Menu not found" );
            return;
        }

        // filepath
        $dst = $args[1] ?? null;
        $dst = empty( $dst )
            ? $this->filenameFromPost( $menu )
            : sanitize_file_name( $dst );


        // write
        $writer = Writer::createFromPath( $dst, 'w+' );
        $writer->insertOne( ['name', 'email'] );
        $writer->insertAll( [
            ['Alice', 'alice@example.com'],
            ['Bob', 'bob@example.com'],
        ] );
    }


    protected function filenameFromPost( \WP_Post $post ) {
        $datetime = date( 'Ymd\THi' );
        return "export-{$post->post_type}_{$post->post_name}_{$post->ID}_{$datetime}.csv";
    }

    /**
     * Test something.
     *
     * @when after_wp_load
     */
    public function test( $args, $assoc_args ) {
        try {

            db::load()->getConnection()->transaction( function () {

                echo "\nTEST -- " . date( 'Y-m-d\@H:i:s' );
                echo "\ncount:" . MenuNode::all()->count();

                // menu
                $menu = MenuPost::find( 'victoria' );

                // parent
                $parent = new MenuNode( [
                    'menu_id' => $menu->ID,
                    'title'   => 'parent',
                    'type'    => 'root',
                ] );
                $parent->saveAsRoot();  // important!
                $parent->refresh();
                MenuNode::fixTree();
                echo "\n" . print_r( $parent->toArray(), true );

                // all nodes
                echo "\ncount:" . MenuNode::all()->count();

                // child
                $child = new MenuNode( [
                    'parent_id' => $parent->id,
                    'menu_id'   => $menu->ID,
                    'title'     => 'child thing',
                    'type'      => 'page',
                ] );
                $child->save();
                $child->refresh();
                echo "\n" . print_r( $child->toArray(), true );

                MenuNode::fixTree();

            } );
        } catch (\Exception $e) {
            echo "\nError: " . $e->getMessage();
            echo "\nFile: " . $e->getFile() . " (Line: " . $e->getLine() . ")";
            echo "\nStack trace: " . $e->getTraceAsString();
        }

    }


}

function printTree( $nodes, $prefix = '' ) {
    foreach ( $nodes as $node ) {
        echo $prefix . $node->title . "\n";
        printTree( $node->children, $prefix . '-- ' );
    }
}