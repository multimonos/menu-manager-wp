<?php

namespace MenuManager\Admin\AdminPages\Menu\Actions;

use MenuManager\Admin\Types\AdminLinkAction;
use MenuManager\Admin\Util\GetActionHelper;
use MenuManager\Model\Menu;
use MenuManager\Model\Post;
use MenuManager\Tasks\Menu\PrintTextMenuTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class PreviewMenuAction implements AdminLinkAction {
    public function id(): string {
        return 'mm_preview_menu';
    }

    public function name(): string {
        return __( 'Preview', 'menu-manager' );
    }

    public function link( Model|Post|\WP_Post $post ): string {

        $url = admin_url( add_query_arg( [
            'page'     => $this->id(),
            'post_id'  => $post->ID,
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
        GetActionHelper::registerHandler( $this );

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

        add_filter( 'post_row_actions', function ( $actions, $post ) {
            return Menu::isType( $post )
                ? $actions + [$this->id() => $this->link( $post )]
                : $actions;
        }, 10, 2 );
    }


    public function handle(): void {
        // Validate
        GetActionHelper::validateOrFail( $this );

        // Get model.
        $menu = GetActionHelper::findPostOrRedirect( Menu::class );

        // preview
        $task = new PrintTextMenuTask();
        echo '<pre>';
        $task->run( intval( $menu->id ) );
        echo '</pre>';
    }
}