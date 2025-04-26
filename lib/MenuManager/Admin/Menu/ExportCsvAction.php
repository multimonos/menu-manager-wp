<?php

namespace MenuManager\Admin\Menu;

use MenuManager\Admin\PostRowAction;
use MenuManager\Model\MenuPost;
use MenuManager\Task\ExportCsvTask;
use MenuManager\Types\ExportMethod;

class ExportCsvAction implements PostRowAction {

    public static function id(): string {
        return 'export_menu_csv';
    }

    public static function link( \WP_Post $post ): string {
        $nonce = wp_create_nonce( self::id() . '_' . $post->ID );
        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            admin_url( sprintf( 'admin-post.php?action=' . self::id() . '&post_id=%s&_wpnonce=%s', $post->ID, $nonce ) ),
            esc_attr( sprintf( __( 'Export "%s" to CSV', 'menu-manager' ), $post->post_title ) ),
            __( 'Export CSV', 'menu-manager' )
        );
    }

    public static function handle(): void {
        // Check if user is allowed
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'menu-manager' ) );
        }

        // Verify parameters
        if ( ! isset( $_GET['post_id'] ) || ! isset( $_GET['_wpnonce'] ) ) {
            wp_die( __( 'Missing required parameters.', 'menu-manager' ) );
        }

        $post_id = intval( $_GET['post_id'] );

        // Verify nonce
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], self::id() . '_' . $post_id ) ) {
            wp_die( __( 'Security check failed.', 'menu-manager' ) );
        }

        // Get the menu
        $menu = get_post( $post_id );
        if ( ! $menu || $menu->post_type !== MenuPost::POST_TYPE ) {
            wp_die( __( 'Menu not found.', 'menu-manager' ) );
        }

        // export
        $path = "menu-export_{$menu->post_name}_{$menu->ID}__" . date( 'Ymd\THis' ) . '.csv';
        $task = new ExportCsvTask();
        $rs = $task->run( ExportMethod::Download, $menu, $path );
        exit;
    }
}