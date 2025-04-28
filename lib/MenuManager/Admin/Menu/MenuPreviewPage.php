<?php

namespace MenuManager\Admin\Menu;

use MenuManager\Admin\Types\AdminPage;
use MenuManager\Admin\Types\PostRowAction;
use MenuManager\Model\MenuPost;
use MenuManager\Task\ViewMenuAsTextTask;


class MenuPreviewPage implements AdminPage, PostRowAction {


    public static function init(): void {
        add_action( 'admin_menu', [self::class, 'add_page'] );
        add_action( 'admin_post_' . self::id(), [self::class, 'handle'] );
    }

    public static function id(): string {
        return 'mm_menu_preview';
    }

    public static function link( \WP_Post $post ): string {

        $url = admin_url( add_query_arg( [
            'page'     => self::id(),
            'menu_id'  => $post->ID,
            '_wpnonce' => wp_create_nonce( self::id() . '_' . $post->ID ),
        ], 'admin.php' ) );

        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            $url,
            esc_attr( sprintf( __( 'Preview "%s"', 'menu-manager' ), $post->post_title ) ),
            __( 'Preview', 'menu-manager' )
        );
    }

    public static function add_page() {
        add_submenu_page( /* parentless page */
            'hidden_menu_preview', // slug must not exist
            __( 'Menu Preview', 'menu-manager' ),
            __( 'Menu Preview', 'menu-manager' ),
            'manage_options',
            self::id(),
            [self::class, 'render']
        );
    }

    public static function handle(): void {
        if ( ! isset( $_GET['menu_id'], $_GET['_wpnonce'] ) ) {
            wp_die( __( 'Missing required parameters.', 'menu-manager' ) );
        }

        // Verify nonce
        $menu_id = intval( $_GET['menu_id'] );
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], self::id() . '_' . $menu_id ) ) {
            wp_die( __( 'Security check failed.', 'menu-manager' ) );
        }

        // Get the menu and display it in a custom format
        $menu = get_post( $menu_id );
        if ( ! $menu || $menu->post_type !== MenuPost::type() ) {
            wp_die( __( 'Menu not found.', 'menu-manager' ) );
        }

        // Render the preview (in your custom format)
        self::render( $menu );
        exit;
    }

    public static function render( $menu = null ): void {
        if ( ! $menu ) {
            $menu_id = intval( $_GET['menu_id'] );
            $menu = get_post( $menu_id );
        }

        if ( ! $menu || $menu->post_type !== MenuPost::type() ) {
            wp_die( __( 'Menu not found.', 'menu-manager' ) );
        }

        // preview
        $task = new ViewMenuAsTextTask();
        echo '<pre>';
        $task->run( intval( $_GET['menu_id'] ) );
        echo '</pre>';
    }
}