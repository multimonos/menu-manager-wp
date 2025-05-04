<?php

namespace MenuManager\Admin;

use MenuManager\Admin\AdminPages\Backup\BackupPageService;
use MenuManager\Admin\AdminPages\Export\ExportPageService;
use MenuManager\Admin\AdminPages\Job\JobPageService;
use MenuManager\Admin\AdminPages\Menu\MenuPageService;
use MenuManager\Admin\Service\NoticeService;
use MenuManager\Admin\Service\SpinnerService;

class AdminService {
    const DATE_FORMAT = 'Y/m/d \a\t g:i a';

    public static function init(): void {

        //User interface
        SpinnerService::init();
        NoticeService::init();

        // Types services
        MenuPageService::init();
        JobPageService::init();
        BackupPageService::init();
        ExportPageService::init();

        add_action( 'admin_head', function () {
            echo '<style>
        .column-date, .column-custom_date_field {
            white-space: nowrap;
        }
    </style>';
        } );
    }
}