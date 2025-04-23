<?php

namespace MenuManager\Wpcli\Commands;

use MenuManager\Database\PostType\MenuPost;
use MenuManager\Task\ExportCsvTask;
use MenuManager\Task\ExportExcelTask;
use MenuManager\Task\ViewMenuAsTextTask;
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
     * [--format=<format>]
     * : Output format. Options: csv, excel. Default: csv.
     *
     * ## EXAMPLES
     *
     *    wp mm export 666 export.csv
     *
     * @when after_wp_load
     */
    public function export( $args, $assoc_args ) {
        $menu_id = $args[0];
        $dst = $args[1] ?? null;
        $format = $assoc_args['format'] ?? 'csv';

        // menu
        $menu = MenuPost::find( $menu_id );

        if ( ! $menu instanceof \WP_Post ) {
            WP_CLI::error( "Menu not found" );
        }

        // file stem
        $filestem = "menu-export_{$menu->post_name}_{$menu->ID}__" . date( 'Ymd\THis' );

        if ( 'csv' === $format ) {
            $path = empty( $dst ) ? $filestem . '.csv' : $dst;
            $task = new ExportCsvTask();
            $rs = $task->run( $menu, $path );

            if ( ! $rs->ok() ) {
                WP_CLI::error( $rs->getMessage() );
            }
            WP_CLI::success( $rs->getMessage() );

        } else if ( 'excel' === $format ) {
            $path = empty( $dst ) ? $filestem . '.xlsx' : $dst;
            $task = new ExportExcelTask();
            $rs = $task->run( $menu, $path );

            if ( ! $rs->ok() ) {
                WP_CLI::error( $rs->getMessage() );
            }
            WP_CLI::success( $rs->getMessage() );
        }
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
        $id = $args[0];
        $pagename = $args[1] ?? null;

        // menu
        $menu = MenuPost::find( $id );

        if ( ! $menu ) {
            WP_CLI::error( "Menu not found '{$id}'." );
        }

        $task = new ViewMenuAsTextTask();
        $rs = $task->run( $menu, $pagename );

        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }
        WP_CLI::success( $rs->getMessage() );
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