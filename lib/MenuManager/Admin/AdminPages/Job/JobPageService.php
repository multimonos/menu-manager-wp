<?php

namespace MenuManager\Admin\AdminPages\Job;

use MenuManager\Admin\AdminPages\Job\Actions\DeleteJobAction;
use MenuManager\Admin\AdminPages\Job\Actions\RunJobAction;
use MenuManager\Admin\AdminPages\Job\Actions\UploadCsvAction;
use MenuManager\Admin\Types\AdminPage;
use MenuManager\Admin\Util\EditScreenHelper;
use MenuManager\Model\Menu;

class JobPageService implements AdminPage {

    protected UploadCsvAction $uploadCsvAction;

    public static function id(): string {
        return 'mm_jobs';
    }

    public static function init(): void {

        $svc = new self;
        $svc->uploadCsvAction = new UploadCsvAction();

        add_action( 'admin_menu', fn() => add_submenu_page(
            'edit.php?post_type=' . Menu::type(),
            'Jobs',
            'Jobs',
            'manage_options',
            self::id(),
            [$svc, 'handle']
        ) );


        EditScreenHelper::registerAdminPostActions( [
            new RunJobAction(),
            new DeleteJobAction(),
            $svc->uploadCsvAction,
        ] );
    }

    public function handle(): void {
        $list_table = new JobListTable();
        $list_table->prepare_items();
        ?>
        <div class="wrap" id="mm-job-list">
            <h1 class="wp-heading-inline">Jobs</h1>
            <?php echo $this->uploadCsvAction->form(); ?>
            <form method="post">
                <?php echo $list_table->display(); ?>
            </form>
        </div>
        <style>
            #mm-job-list .tablenav {
                display: none;
            }

            #mm-job-list .wp-list-table {
                margin-top: 1rem;
            }

            #mm-job-list .create-wrap {
                display: flex;
                justify-content: flex-end;
            }

            #mm-job-list .column-id {
                width: 4rem;
            }

            #mm-job-list .column-created_at,
            #mm-job-list .column-lastrun_at {
                white-space: nowrap;
            }
        </style>
        <?php
    }
}