<?php

namespace MenuManager\Admin;

use MenuManager\Admin\Backup\BackupService;
use MenuManager\Admin\Impex\ImpexPageService;
use MenuManager\Admin\Job\JobPageService;
use MenuManager\Admin\Menu\MenuPageService;
use MenuManager\Admin\Service\NoticeService;
use MenuManager\Admin\Service\SpinnerService;

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