<?php

namespace MenuManager\Admin\AdminPages\Job;

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
            $svc->uploadCsvAction,
        ] );
    }

    public function handle(): void {
        $list_table = new JobListTable();
        $list_table->prepare_items();

        echo '<div class="wrap"><h1 class="wp-heading-inline">Jobs</h1>';
        echo $this->uploadCsvAction->form();
        echo '<form method="post">';
        $list_table->display();
        echo '</form></div>';
    }
}