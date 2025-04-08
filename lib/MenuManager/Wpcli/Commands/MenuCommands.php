<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Database\db;
use MenuManager\Database\PostType\MenuPost;
use MenuManager\Wpcli\TextMenuPrinter;
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
            WP_CLI::runcommand( "post get {$post->ID} --format=table" );
        }
    }


    /**
     * Render a menu to stdout.
     *
     * ## OPTIONS
     *
     * <id>
     *  : The id or slug of the menu ot get.
     *
     * ## EXAMPLES
     *
     *      wp mm menus view foobar
     *
     * @when after_wp_load
     */
    public function view( $args, $assoc_args ) {
        db::load();

        $id = $args[0];

        // menu
        $menu = MenuPost::find( $id );

        // printer
        $printer = new TextMenuPrinter();
        $printer->print( $menu );
    }
}
