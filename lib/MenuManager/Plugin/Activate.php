<?php

namespace MenuManager\Plugin;

use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Job;
use MenuManager\Database\Model\MenuCategory;
use MenuManager\Database\Model\MenuItem;
use MenuManager\Database\Model\MenuPage;

class Activate {

    public static function run() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $o = new self;
        $o->log( "activated" );
        $o->create_database();
    }

    public function log( $msg ) {
        error_log( "menu-manager: " . $msg );
    }

    public function create_database() {
        global $wpdb;

        Job::createTable();
        Impex::createTable();
        MenuPage::createTable();
        MenuCategory::createTable();
        MenuItem::createTable();
    }
}