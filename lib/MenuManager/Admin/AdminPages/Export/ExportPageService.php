<?php

namespace MenuManager\Admin\AdminPages\Export;

use MenuManager\Admin\AdminPages\Export\Actions\ExportCustomAction;
use MenuManager\Admin\AdminPages\Export\Actions\UploadCsvAction;
use MenuManager\Admin\Types\AdminPage;
use MenuManager\Admin\Util\EditScreenHelper;
use MenuManager\Model\Menu;

class ExportPageService implements AdminPage {

    protected ExportCustomAction $exportAction;

    public static function id(): string {
        return 'mm_export';
    }

    public static function init(): void {

        $svc = new self;
        $svc->exportAction = new ExportCustomAction();

        add_action( 'admin_menu', fn() => add_submenu_page(
            'edit.php?post_type=' . Menu::type(),
            'Export',
            'Export',
            'manage_options',
            self::id(),
            [$svc, 'handle']
        ) );


        EditScreenHelper::registerAdminPostActions( [
            $svc->exportAction,
        ] );
    }

    public function handle(): void {
        ?>
        <div class="wrap" id="<?php echo $this->id(); ?>">
            <h1 class="wp-heading-inline">Export</h1>
            <?php echo $this->exportAction->form(); ?>
        </div>

        <?php
    }
}