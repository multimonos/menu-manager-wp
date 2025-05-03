<?php

namespace MenuManager\Admin\AdminPages\Menu\Actions;

use MenuManager\Admin\Types\AdminLinkAction;
use MenuManager\Admin\Util\GetActionHelper;
use MenuManager\Model\Menu;
use MenuManager\Model\Post;
use MenuManager\Tasks\Menu\CloneMenuTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class CloneMenuAction implements AdminLinkAction {

    public function id(): string {
        return 'mm_clone_menu';
    }

    public function name(): string {
        return __( 'Clone', 'menu-manager' );
    }

    public function register(): void {
        GetActionHelper::registerHandler( $this );

        add_filter( 'post_row_actions', function ( $actions, $post ) {
            return Menu::isType( $post )
                ? $actions + [$this->id() => $this->link( $post )]
                : $actions;
        }, 10, 2 );
    }

    public function link( Model|Post|\WP_Post $model ): string {
        return GetActionHelper::createLink( $this, $model, true );
    }

    public function handle(): void {
        // Validate
        GetActionHelper::validateOrFail( $this );

        // Get model.
        $menu = GetActionHelper::findPostOrRedirect( Menu::class );

        // Run
        $src = $menu->post->post_name;
        $dst = $menu->post->post_name . '-' . uniqid();

        $task = new CloneMenuTask();
        $rs = $task->run( $src, $dst );

        // Send result.
        GetActionHelper::sendResult( $rs );
    }
}