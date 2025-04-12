<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Database\PostType\MenuPost;
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
     *      wp mm jobs list
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
     *  : The id or slug of the menu ot get.
     *
     * ## EXAMPLES
     *
     *      wp mm menus get 42
     *      wp mm menus get foobar
     *
     * @when after_wp_load
     */
    public function get( $args, $assoc_args ) {
        $id = $args[0];

        if ( is_numeric( $id ) ) {
            WP_CLI::runcommand( "post get {$id} --format=table" );
        } else {
            $post = MenuPost::find( $id );

            if ( $post instanceof \WP_Post ) {
                WP_CLI::runcommand( "post get {$post->ID} --format=table" );
            } else {
                WP_CLI::error( "Menu not found '$id'." );
            }
        }
    }

}
