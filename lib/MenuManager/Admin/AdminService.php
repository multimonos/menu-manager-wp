<?php

namespace MenuManager\Admin;

use MenuManager\Admin\Backup\BackupService;
use MenuManager\Admin\Service\ImpexPageService;
use MenuManager\Admin\Service\JobPageService;
use MenuManager\Admin\Service\MenuPageService;
use MenuManager\Admin\Service\UserInterface\NoticeService;
use MenuManager\Admin\Service\UserInterface\SpinnerService;

class AdminService {
    public static function init(): void {


        //User interface
        SpinnerService::init();
        NoticeService::init();

        // Types services
        MenuPageService::init();
        ImpexPageService::init();
        JobPageService::init();
        BackupService::init();

        add_action( 'admin_head', function () {
            echo '<style>
        .column-date, .column-custom_date_field {
            white-space: nowrap;
        }
    </style>';
        } );
    }
}