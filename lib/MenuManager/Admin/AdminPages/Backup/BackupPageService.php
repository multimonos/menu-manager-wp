<?php

namespace MenuManager\Admin\AdminPages\Backup;

use MenuManager\Admin\AdminPages\Backup\Actions\CreateBackupAction;
use MenuManager\Admin\AdminPages\Backup\Actions\DeleteBackupAction;
use MenuManager\Admin\AdminPages\Backup\Actions\RestoreBackupAction;
use MenuManager\Admin\Types\AdminPage;
use MenuManager\Admin\Util\EditScreenHelper;
use MenuManager\Model\Menu;

class BackupPageService implements AdminPage {
    protected CreateBackupAction $createAction;

    public static function id(): string {
        return 'mm_backup';
    }

    public static function init(): void {

        $svc = new self;
        $svc->createAction = new CreateBackupAction();

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
            $svc->createAction,
        ] );
    }

    public function handle(): void {
        $list_table = new BackupListTable();
        $list_table->prepare_items();
        ?>

        <div class="wrap" id="mm-backup-list">

            <h1 class="wp-heading-inline">Backups</h1>
            <hr class="wp-header-end">

            <div class="create-wrap">
                <?php echo $this->createAction->form(); ?>
            </div>

            <form method="post">
                <?php echo $list_table->display(); ?>
            </form>
        </div>
        <style>
            #mm-backup-list .tablenav {
                display: none;
            }

            #mm-backup-list .wp-list-table {
                margin-top: 1rem;
            }

            #mm-backup-list .create-wrap {
                display: flex;
                justify-content: flex-end;
            }

            #mm-backup-list .column-id {
                width: 4rem;
            }

            #mm-backup-list .column-created_at,
            #mm-backup-list .column-lastrun_at {
                width: 12rem;
            }
        </style>
        <?php
    }

}