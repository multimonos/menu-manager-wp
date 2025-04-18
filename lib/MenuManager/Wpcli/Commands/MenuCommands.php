<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Database\PostType\MenuPost;
use MenuManager\Task\CloneMenuTask;
use WP_CLI;

class MenuCommands {

    /**
     * Get a list of menus.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format. Options: table, ids. Default: table.
     *
     * ## EXAMPLES
     *
     *      wp mm menu list
     *
     * @when after_wp_load
     */
    public function list( $args, $assoc_args ) {
        $format = $assoc_args['format'] ?? 'table';
        WP_CLI::runcommand( "post list --post_type=menus --format={$format}" );
    }

    /**
     * Get details about a menu.
     *
     * ## OPTIONS
     *
     * <id>
     * : The id or slug of the menu ot get.
     *
     * ## EXAMPLES
     *
     *      wp mm menu get 42
     *      wp mm menu get foobar
     *
     * @when after_wp_load
     */
    public function get( $args, $assoc_args ) {
        $id = $args[0];

        if ( is_numeric( $id ) ) {
            WP_CLI::runcommand( "post get {$id} --format=json" );
        } else {
            $post = MenuPost::find( $id );

            if ( $post instanceof \WP_Post ) {
                WP_CLI::runcommand( "post get {$post->ID} --format=json" );
            } else {
                WP_CLI::error( "Menu not found '$id'." );
            }
        }
    }

    /**
     * Get details about a menu.
     *
     * ## OPTIONS
     *
     * <source_slug>
     * : The id or slug of the source menu.
     *
     * <target_slug>
     * : The slug of new menu.
     *
     * ## EXAMPLES
     *
     *      wp mm menu clone 42 foobar
     *      wp mm menu clone bam foobar
     *
     * @when after_wp_load
     */
    public function clone( $args, $assoc_args ) {
        list( $src, $dst ) = $args;

        $task = new CloneMenuTask();
        $rs = $task->run( $src, $dst );

        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }

        array_map( fn( $x ) => WP_CLI::line( $x ), $rs->getData() );
        WP_CLI::success( $rs->getMessage() );
    }


}
