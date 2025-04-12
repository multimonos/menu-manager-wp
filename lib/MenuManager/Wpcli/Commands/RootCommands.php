<?php

namespace MenuManager\Wpcli\Commands;

use MenuManager\Actions\ExportAction;
use MenuManager\Database\db;
use MenuManager\Database\Model\Node;
use MenuManager\Database\PostType\MenuPost;
use MenuManager\Wpcli\TextMenuPrinter;
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
        $menu_id = $args[0];

        // menu
        $menu = MenuPost::find( $menu_id );

        if ( ! $menu instanceof \WP_Post ) {
            WP_CLI::error( "Menu not found" );
        }

        // output path
        $dst = $args[1] ?? null;
        $dst = empty( $dst )
            ? "menu-export_{$menu->post_name}_{$menu->ID}__" . date( 'Ymd\THis' ) . '.csv'
            : sanitize_file_name( $dst );

        // action
        $action = new ExportAction();
        $rs = $action->run( $menu, $dst );

        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }

        WP_CLI::success( $rs->getMessage() );
    }


    /**
     * Print menu to stdout.
     *
     * ## OPTIONS
     *
     * <id>
     * : The id or slug of the menu to get.
     *
     * [<page>]
     * : The page to fetch
     *
     * ## EXAMPLES
     *
     *   wp mm view crowfoot
     *   wp mm view crowfoot drink
     *
     * @when after_wp_load
     */
    public function view( $args, $assoc_args ) {

        // @todo refactor into action

        db::load()::connection()->enableQueryLog();

        $id = $args[0];
        $page = $args[1] ?? null;

        // menu
        $menu = MenuPost::find( $id );

        if ( ! $menu ) {
            WP_CLI::error( "Menu not found '{$id}'." );
        }

        // tree
        $tree = empty( $page )
            ? Node::findRootTree( $menu )
            : Node::findPageTree( $menu, $page );

        if ( ! $tree || $tree->count() === 0 ) {
            WP_CLI::error( "Menu not found or is empty '" . trim( $id . ' ' . $page ) . "'." );
        }


        // printer
        $printer = new TextMenuPrinter();

        // print
        echo "\n$id $page";
        $printer->print( $tree );

        // log
        $queries = db::load()::connection()->getQueryLog();

        echo "\n\n";
        WP_CLI::line( "Nodes: " . $tree->count() );
        WP_CLI::success( count( $queries ) . ' queries.' );
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
                echo "\ncount:" . Node::all()->count();

                // menu
                $menu = MenuPost::find( 'victoria' );

                // parent
                $parent = new Node( [
                    'menu_id' => $menu->ID,
                    'title'   => 'parent',
                    'type'    => 'root',
                ] );
                $parent->saveAsRoot();  // important!
                $parent->refresh();
                Node::fixTree();
                echo "\n" . print_r( $parent->toArray(), true );

                // all nodes
                echo "\ncount:" . Node::all()->count();

                // child
                $child = new Node( [
                    'parent_id' => $parent->id,
                    'menu_id'   => $menu->ID,
                    'title'     => 'child thing',
                    'type'      => 'page',
                ] );
                $child->save();
                $child->refresh();
                echo "\n" . print_r( $child->toArray(), true );

                Node::fixTree();

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