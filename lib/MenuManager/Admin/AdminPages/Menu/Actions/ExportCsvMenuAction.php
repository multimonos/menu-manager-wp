<?php

namespace MenuManager\Admin\AdminPages\Menu\Actions;

use MenuManager\Admin\Types\AdminLinkAction;
use MenuManager\Admin\Util\GetActionHelper;
use MenuManager\Model\Menu;
use MenuManager\Model\Post;
use MenuManager\Tasks\Menu\ExportTask;
use MenuManager\Types\Export\ExportConfig;
use MenuManager\Types\Export\ExportContext;
use MenuManager\Types\Export\ExportFormat;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class ExportCsvMenuAction implements AdminLinkAction {

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

        // Config
        $config = new ExportConfig();
        $config->menus = [$menu->id];
        $config->context = ExportContext::Download;
        $config->format = ExportFormat::Csv;

        // Task
        $task = new ExportTask();
        $rs = $task->run( $config );

        exit;
    }
}