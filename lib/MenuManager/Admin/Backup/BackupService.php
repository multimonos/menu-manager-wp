<?php

namespace MenuManager\Admin\Backup;

use MenuManager\Admin\Backup\Actions\DeleteBackupAction;
use MenuManager\Admin\Backup\Actions\RestoreBackupAction;
use MenuManager\Admin\Types\AdminPage;
use MenuManager\Admin\Util\EditScreenHelper;
use MenuManager\Model\Menu;

class BackupService implements AdminPage {
    public static function id(): string {
        return 'mm_backup';
    }

    public static function init(): void {

        $svc = new self;

        add_action( 'admin_menu', fn() => add_submenu_page(
            'edit.php?post_type=' . Menu::type(),
            'Backup / Restore',
            'Backup / Restore',
            'manage_options',
            self::id(),
            [$svc, 'handle']
        ) );

        EditScreenHelper::registerAdminPostActions( [
            new RestoreBackupAction(),
            new DeleteBackupAction(),
        ] );
    }

    public function handle(): void {
        $list_table = new BackupListTable();
        $list_table->prepare_items();

        echo '<div class="wrap"><h1 class="wp-heading-inline">Backups</h1>';
        echo '<form method="post">';
        $list_table->display();
        echo '</form></div>';

    }

}