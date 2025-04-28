<?php

namespace MenuManager\Admin;

use MenuManager\Admin\Job\JobCreatePage;
use MenuManager\Admin\Job\JobListPage;
use MenuManager\Admin\Menu\MenuListPage;
use MenuManager\Admin\Menu\MenuPreviewPage;
use MenuManager\Admin\Service\NoticeService;

class AdminService {
    public static function init(): void {
        add_action( 'admin_notices', [NoticeService::class, 'show'] );

        // Menus
        MenuListPage::init();
        MenuPreviewPage::init();

        // Jobs
        JobCreatePage::init();
        JobListPage::init();
    }
}