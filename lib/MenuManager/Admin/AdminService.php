<?php

namespace MenuManager\Admin;

use MenuManager\Admin\Menu\MenuListPage;
use MenuManager\Admin\Menu\MenuPreviewPage;
use MenuManager\Admin\Service\NoticeService;

class AdminService {
    public static function init(): void {
        add_action( 'admin_notices', [NoticeService::class, 'show'] );

        // menus
        MenuListPage::init();
        MenuPreviewPage::init();
    }
}