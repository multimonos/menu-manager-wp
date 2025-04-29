<?php

namespace MenuManager\Admin\Service;

use MenuManager\Admin\Actions\UploadCsvAction;
use MenuManager\Admin\Types\AdminPage;
use MenuManager\Model\Menu;

class ImpexPageService implements AdminPage {


    protected UploadCsvAction $uploadAction;


    public static function id(): string {
        return 'mm_impex';
    }

    public static function init(): void {

        $svc = new self;

        //csv upload
        $svc->uploadAction = new UploadCsvAction();
        $svc->uploadAction->redirectUrl = admin_url( add_query_arg( [
            'post_type' => Menu::type(),
            'page'      => self::id(),
        ], 'edit.php' ) );
        $svc->uploadAction->register();

        add_action( 'admin_menu', fn() => add_submenu_page(
            'edit.php?post_type=' . Menu::type(),
            'Impex',
            'Impex',
            'manage_options',
            self::id(),
            [$svc, 'handle']
        ) );
    }

    public function handle(): void {
        ?>
        <div class="wrap">
        <h1>Import / Export</h1>

        <?php echo $this->uploadAction->form(); ?>
        <?php
    }
}