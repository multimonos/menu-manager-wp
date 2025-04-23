<?php

namespace MenuManager\Wpcli\Commands;

use MenuManager\Database\PostType\MenuPost;
use MenuManager\Task\ExportCsvTask;
use MenuManager\Task\ExportExcelTask;
use MenuManager\Task\LoadTask;
use MenuManager\Types\ExportMethod;
use WP_CLI;

class RootCommands {

    /**
     * Load a CSV to create an import job.
     *
     * ## OPTIONS
     *
     * <file>
     * : The CSV file to consume.
     *
     * ## EXAMPLES
     *
     *      wp mm import load impex-foobar.csv
     *
     * @when after_wp_load
     */
    public function load( $args, $assoc_args ) {
        list( $path ) = $args;

        // guard : file
        if ( ! file_exists( $path ) ) {
            WP_CLI::error( "File not found $path" );
        }

        // load
        $task = new LoadTask();
        $rs = $task->run( $path );

        // guard : err
        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }
        WP_CLI::success( $rs->getMessage() );
    }

    /**
     * Export menu to CSV or Excel.
     *
     * ## OPTIONS
     *
     * <menu_id>
     * : ID of the menu.
     *
     * [<file>]
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
            $rs = $task->run( ExportMethod::File, $menu, $path );

            if ( ! $rs->ok() ) {
                WP_CLI::error( $rs->getMessage() );
            }
            WP_CLI::success( $rs->getMessage() );

        } else if ( 'excel' === $format ) {
            $path = empty( $dst ) ? $filestem . '.xlsx' : $dst;
            $task = new ExportExcelTask();
            $rs = $task->run( ExportMethod::File, $menu, $path );

            if ( ! $rs->ok() ) {
                WP_CLI::error( $rs->getMessage() );
            }
            WP_CLI::success( $rs->getMessage() );
        }
    }


}