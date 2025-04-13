<?php

namespace MenuManager\Wpcli\Commands;

use MenuManager\Database\db;
use MenuManager\Database\Model\Node;
use MenuManager\Database\PostType\MenuPost;
use MenuManager\Task\ExportTask;
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
        $task = new ExportTask();
        $rs = $task->run( $menu, $dst );

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
        WP_CLI::success( "Nothing to do." );
    }
}