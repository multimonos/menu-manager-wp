<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Database\PostType\Menu;
use WP_CLI;

class MenuCommands {

    /**
     * Get a list of menus.
     *
     * @when after_wp_load
     */
    public function list( $args, $assoc_args ) {
        WP_CLI::runcommand( 'post list --post_type=menus --format=table' );
    }

    /**
     * Get details about a menu.
     *
     * ## OPTIONS
     *
     * <id>
     *  : The id of the post ot get.
     *
     * @when after_wp_load
     */
    public function get( $args, $assoc_args ) {
        $id = $args[0];

        if ( is_numeric( $id ) ) {
            WP_CLI::runcommand( "post get {$id} --format=table" );
        } else {
            $post = Menu::find( $id );
            WP_CLI::runcommand( "post get {$post->ID} --format=table" );
        }
    }
}
