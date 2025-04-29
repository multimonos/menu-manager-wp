<?php

namespace MenuManager\Admin\Actions;

use MenuManager\Admin\Types\AdminPostLinkAction;
use MenuManager\Model\Menu;
use MenuManager\Task\ViewMenuAsTextTask;

class PreviewMenuAction implements AdminPostLinkAction {
    public function id(): string {
        return 'mm_preview_menu';
    }

    public function link( \WP_Post $post ): string {

        $url = admin_url( add_query_arg( [
            'page'     => $this->id(),
            'menu_id'  => $post->ID,
            '_wpnonce' => wp_create_nonce( $this->id() . '_' . $post->ID ),
        ], 'admin.php' ) );

        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            $url,
            esc_attr( sprintf( __( 'Preview "%s"', 'menu-manager' ), $post->post_title ) ),
            __( 'Preview', 'menu-manager' )
        );
    }


    public function register(): void {

        add_action( 'admin_menu', function () {
            add_submenu_page( /* parentless page */
                $this->id() . '-680feafbbb178', /* slug must not exist */
                __( 'Menu Preview', 'menu-manager' ),
                __( 'Menu Preview', 'menu-manager' ),
                'manage_options',
                $this->id(),
                [$this, 'handle']
            );
        } );

        add_action( 'admin_post_' . $this->id(), [$this, 'handle'] );

        add_filter( 'post_row_actions', function ( $actions, $post ) {
            return Menu::isType( $post )
                ? $actions + [$this->id() => $this->link( $post )]
                : $actions;
        }, 10, 2 );
    }


    public function handle(): void {
        if ( ! isset( $_GET['menu_id'], $_GET['_wpnonce'] ) ) {
            wp_die( __( 'Missing required parameters.', 'menu-manager' ) );
        }

        // Verify nonce
        $menu_id = intval( $_GET['menu_id'] );
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], $this->id() . '_' . $menu_id ) ) {
            wp_die( __( 'Security check failed.', 'menu-manager' ) );
        }

        // Get the menu and display it in a custom format
        $menu = get_post( $menu_id );
        if ( ! $menu || $menu->post_type !== Menu::type() ) {
            wp_die( __( 'Menu not found.', 'menu-manager' ) );
        }

        // preview
        $task = new ViewMenuAsTextTask();
        echo '<pre>';
        $task->run( intval( $_GET['menu_id'] ) );
        echo '</pre>';
    }
}