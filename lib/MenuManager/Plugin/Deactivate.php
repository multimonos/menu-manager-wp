<?php

namespace MenuManager\Plugin;

use MenuManager\Database\db;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Job;
use MenuManager\Database\Model\MenuCategory;
use MenuManager\Database\Model\MenuItem;
use MenuManager\Database\Model\MenuPage;

class Deactivate {
    public static function run() {
        $o = new self;
        $o->log( "deactivated" );
        $o->cleanDatabase();
    }

    public function log( $msg ) {
        error_log( "menu-manager: " . $msg );
    }

    public function cleanDatabase() {
        global $wpdb;

        db::load()->getConnection()->statement( 'SET foreign_key_checks=0;' );
        db::load()::schema()->dropIfExists( Job::TABLE );
        db::load()::schema()->dropIfExists( Impex::TABLE );
        db::load()::schema()->dropIfExists( MenuItem::TABLE );
        db::load()::schema()->dropIfExists( MenuCategory::TABLE );
        db::load()::schema()->dropIfExists( MenuPage::TABLE );
        db::load()->getConnection()->statement( 'SET foreign_key_checks=1;' );
    }
}