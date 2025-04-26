<?php

namespace MenuManager\Admin;

use MenuManager\Admin\Menu\MenuListPage;
use MenuManager\Admin\Service\NoticeService;

class AdminService {
    public static function init(): void {
        add_action( 'admin_notices', [NoticeService::class, 'show'] );
        MenuListPage::init();
    }
}