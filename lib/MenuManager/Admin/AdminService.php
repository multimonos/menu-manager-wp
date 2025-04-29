<?php

namespace MenuManager\Admin;

use MenuManager\Admin\Service\ImpexPageService;
use MenuManager\Admin\Service\JobPageService;
use MenuManager\Admin\Service\MenuPageService;
use MenuManager\Admin\Service\NoticeService;

class AdminService {
    public static function init(): void {
        add_action( 'admin_notices', [NoticeService::class, 'show'] );

        MenuPageService::init();
        ImpexPageService::init();
        JobPageService::init();

        add_action( 'admin_head', function () {
            echo '<style>
        .column-date, .column-custom_date_field {
            white-space: nowrap;
        }
    </style>';
        } );
    }
}