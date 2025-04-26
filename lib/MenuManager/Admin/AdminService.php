<?php

namespace MenuManager\Admin;

use MenuManager\Admin\Menu\MenuService;
use MenuManager\Admin\Service\NoticeService;

class AdminService {
    public static function init(): void {
        MenuService::init();
        add_action( 'admin_notices', [NoticeService::class, 'show'] );
    }
}