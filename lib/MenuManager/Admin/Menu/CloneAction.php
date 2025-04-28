<?php

namespace MenuManager\Admin\Menu;

use MenuManager\Admin\Service\NoticeService;
use MenuManager\Admin\Types\PostRowAction;
use MenuManager\Model\Menu;
use MenuManager\Task\CloneMenuTask;

class CloneAction implements PostRowAction {

    public static function id(): string {
        return 'clone_menu';
    }

    public static function link( \WP_Post $post ): string {
        $url = admin_url( add_query_arg( [
            'action'   => self::id(),
            'menu_id'  => $post->ID,
            '_wpnonce' => wp_create_nonce( self::id() . '_' . $post->ID ),
        ], 'admin-post.php' ) );

        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            $url,
            esc_attr( sprintf( __( 'Clone "%s"', 'menu-manager' ), $post->post_title ) ),
            __( 'Clone', 'menu-manager' )
        );
    }

    public static function handle(): void {
        // Check if user is allowed
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'menu-manager' ) );
        }

        // Verify parameters
        if ( ! isset( $_GET['menu_id'] ) || ! isset( $_GET['_wpnonce'] ) ) {
            wp_die( __( 'Missing required parameters.', 'menu-manager' ) );
        }

        $menu_id = intval( $_GET['menu_id'] );

        // Verify nonce
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], self::id() . '_' . $menu_id ) ) {
            wp_die( __( 'Security check failed.', 'menu-manager' ) );
        }

        // Get the menu
        $menu = Menu::find( $menu_id );
        if ( $menu === null ) {
            wp_die( __( 'Menu not found.', 'menu-manager' ) );
        }

        // export
        $src = $menu->post->post_name;
        $dst = $menu->post->post_name . '-copy';

        $task = new CloneMenuTask();
        $rs = $task->run( $src, $dst );

        if ( $rs->ok() ) {
            NoticeService::success( $rs->getMessage() );
        } else {
            NoticeService::error( $rs->getMessage() );
        }

        wp_redirect( admin_url( 'edit.php?post_type=' . Menu::type() ) );

    }
}