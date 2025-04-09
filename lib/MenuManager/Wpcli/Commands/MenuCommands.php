<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Database\db;
use MenuManager\Database\Model\Node;
use MenuManager\Database\PostType\MenuPost;
use MenuManager\Wpcli\TextMenuPrinter;
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


    /**
     * Render a menu to stdout.
     *
     * ## OPTIONS
     *
     * <id>
     * : The id or slug of the menu ot get.
     *
     * [<page>]
     * : The page to fetch
     *
     * ## EXAMPLES
     *
     *   wp mm menus view crowfoot
     *   wp mm menus view crowfoot drink
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
        WP_CLI::success( count( $queries ) . ' queries.' );
    }
}
