<?php

namespace MenuManager\Admin\Actions;

use MenuManager\Admin\Types\AdminPostLinkAction;
use MenuManager\Model\Menu;
use MenuManager\Model\Post;
use MenuManager\Task\ExportExcelTask;
use MenuManager\Types\ExportMethod;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class ExportExcelMenuAction implements AdminPostLinkAction {

    public function id(): string {
        return 'mm_export_excel';
    }

    public function register(): void {
        add_action( 'admin_post_' . $this->id(), [$this, 'handle'] );

        add_filter( 'post_row_actions', function ( $actions, $post ) {
            return Menu::isType( $post )
                ? $actions + [$this->id() => $this->link( $post )]
                : $actions;
        }, 10, 2 );
    }


    public function link( Model|Post|\WP_Post $post ): string {
        $url = admin_url( add_query_arg( [
            'action'   => $this->id(),
            'menu_id'  => $post->ID,
            '_wpnonce' => wp_create_nonce( $this->id() . '_' . $post->ID ),
        ], 'admin-post.php' ) );

        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            $url,
            esc_attr( sprintf( __( 'Export "%s" to Excel', 'menu-manager' ), $post->post_title ) ),
            __( 'Export Excel', 'menu-manager' )
        );
    }

    public function handle(): void {
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
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], $this->id() . '_' . $menu_id ) ) {
            wp_die( __( 'Security check failed.', 'menu-manager' ) );
        }

        // Get the menu
        $menu = Menu::find( $menu_id );
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