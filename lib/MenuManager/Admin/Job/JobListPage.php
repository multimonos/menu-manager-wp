<?php

namespace MenuManager\Admin\Job;

use MenuManager\Admin\Types\AdminPage;

class JobListPage implements AdminPage {
    public static function id(): string {
        return 'mm_job_list';
    }

    public static function init(): void {
        $svc = new self;
    }
}