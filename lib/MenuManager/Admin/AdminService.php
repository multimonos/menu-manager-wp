<?php

namespace MenuManager\Admin;

use MenuManager\Admin\Job\ImpexPageService;
use MenuManager\Admin\Menu\MenuPageService;
use MenuManager\Admin\Service\NoticeService;

class AdminService {
    public static function init(): void {
        add_action( 'admin_notices', [NoticeService::class, 'show'] );

        MenuPageService::init();
        ImpexPageService::init();
    }
}