<?php

namespace MenuManager\Admin\Menu;

use MenuManager\Admin\Types\PostRowAction;
use MenuManager\Model\Menu;
use MenuManager\Task\ExportExcelTask;
use MenuManager\Types\ExportMethod;

class ExportExcelAction implements PostRowAction {

    public static function id(): string {
        return 'export_menu_excel';
    }

    public static function link( \WP_Post $post ): string {
        $url = admin_url( add_query_arg( [
            'action'   => self::id(),
            'post_id'  => $post->ID,
            '_wpnonce' => wp_create_nonce( self::id() . '_' . $post->ID ),
        ], 'admin-post.php' ) );

        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            $url,
            esc_attr( sprintf( __( 'Export "%s" to Excel', 'menu-manager' ), $post->post_title ) ),
            __( 'Export Excel', 'menu-manager' )
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
        $menu = Menu::find( $post_id );
        if ( $menu === null ) {
            wp_die( __( 'Menu not found.', 'menu-manager' ) );
        }

        // export
        $path = "menu-export_{$menu->post->post_name}_{$menu->post->ID}__" . date( 'Ymd\THis' ) . '.xlsx';
        $task = new ExportExcelTask();
        $rs = $task->run( ExportMethod::Download, $menu, $path );
        exit;
    }
}