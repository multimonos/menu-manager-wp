<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Model\Menu;
use MenuManager\Tasks\Generic\ListPostsTask;
use MenuManager\Tasks\Menu\CloneMenuTask;
use MenuManager\Tasks\Menu\PrintTextMenuTask;
use MenuManager\Wpcli\Util\CommandHelper;
use WP_CLI;

class MenuCommands {

    /**
     * Get a list of menus.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format. Options: table, ids. Default: json.
     *
     * ## EXAMPLES
     *
     *      wp mm menu list
     *
     * @when after_wp_load
     */
    public function ls( $args, $assoc_args ) {
        $format = $assoc_args['format'] ?? 'table';
        $fields = [
            'ID',
            'post_title',
            'post_name',
        ];

        $task = new ListPostsTask();
        $rs = $task->run( Menu::class, $fields, $format );

        CommandHelper::sendDataOnly( $rs );
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
            $menu = Menu::find( $id );

            if ( $menu === null ) {
                WP_CLI::error( "Menu not found '$id'." );
            }
            WP_CLI::runcommand( "post get {$menu->post->ID} --format=json" );
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

        CommandHelper::sendTaskResult( $rs );
    }


    /**
     * Delete a menu.
     *
     * ## OPTIONS
     *
     * <id>
     * : The id of the menu.
     *
     * @when after_wp_load
     */
    public function rm( $args, $assoc_args ) {
        $id = $args[0];

        if ( ! is_numeric( $id ) ) {
            WP_CLI::error( "Delete requires a numeric id." );
        }

        WP_CLI::runcommand( "post delete {$id} --force" );
    }

    /**
     * Print menu to stdout.
     *
     * ## OPTIONS
     *
     * <menu_id>
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

        $task = new PrintTextMenuTask();
        $rs = $task->run( $id, $pagename );

        CommandHelper::sendTaskResult( $rs );
    }
}
