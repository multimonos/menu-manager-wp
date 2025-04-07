<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Database\PostType\MenuPost;
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
        $id = $args[0];

        $menu = MenuPost::find( $id );

        $pages = MenuPost::menuPages( $menu );
        print_r( $pages->count() );

        $pages->each( function ( $page ) {
            echo "\nPa {$page->slug}";

            $page->menuCategories()->each( function ( $category ) {
                $indent = str_repeat( '  ', $category->level );

                echo "\n{$indent}C{$category->level} {$category->title} $[$category->prices]";

                echo "\n{$indent}  {$category->menuItems()->count()}";

                $category->menuItems()->each( function ( $item, $k ) use ( $indent ) {
                    $n = str_pad( $k + 1, 2, );
                    echo "\n{$indent}  {$n}  It {$item->title} $[{$item->prices}]";

                } );
            } );

        } );

    }
}
