<?php

namespace MenuManager\Admin\AdminPages\Menu\Actions;

use MenuManager\Admin\Service\NoticeService;
use MenuManager\Admin\Types\AdminPostLinkAction;
use MenuManager\Model\Menu;
use MenuManager\Model\Post;
use MenuManager\Tasks\Menu\CloneMenuTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class CloneMenuAction implements AdminPostLinkAction {

    public function id(): string {
        return 'mm_clone_menu';
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
            'post_id'  => $post->ID,
            '_wpnonce' => wp_create_nonce( $this->id() . '_' . $post->ID ),
        ], 'admin-post.php' ) );

        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            $url,
            esc_attr( sprintf( __( 'Clone "%s"', 'menu-manager' ), $post->post_title ) ),
            __( 'Clone', 'menu-manager' )
        );
    }

    public function handle(): void {
//        GetActionHelper::validateOrFail( $this, true );
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
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], $this->id() . '_' . $post_id ) ) {
            wp_die( __( 'Security check failed.', 'menu-manager' ) );
        }

        // Get the menu
        $post_id = intval( $_GET['post_id'] );
        $menu = Menu::find( $post_id );
        if ( $menu === null ) {
            wp_die( __( 'Menu not found.', 'menu-manager' ) );
        }

        // export
        $src = $menu->post->post_name;
        $dst = $menu->post->post_name . '-' . uniqid();

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