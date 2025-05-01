<?php

namespace MenuManager\Admin\AdminPages\Menu\Actions;

use MenuManager\Admin\Types\AdminPostLinkAction;
use MenuManager\Admin\Util\GetActionHelper;
use MenuManager\Model\Menu;
use MenuManager\Model\Post;
use MenuManager\Tasks\Menu\ExportMenuAsCsvTask;
use MenuManager\Types\ExportMethod;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class ExportCsvMenuAction implements AdminPostLinkAction {

    public function id(): string {
        return 'mm_export_csv';
    }

    public function name(): string {
        return __( 'Export CSV', 'menu-manager' );
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
        return GetActionHelper::createLink( $this, $model );
    }

    public function handle(): void {
        // Validate
        GetActionHelper::validateOrFail( $this );

        // Get model.
        $menu = GetActionHelper::findPostOrRedirect( Menu::class );

        // export
        $path = "menu-export_{$menu->post->post_name}_{$menu->post->ID}__" . date( 'Ymd\THis' ) . '.csv';
        $task = new ExportMenuAsCsvTask();
        $rs = $task->run( ExportMethod::Download, $menu, $path );
        exit;
    }
}