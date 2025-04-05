<?php

namespace MenuManager\Plugin;

use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Job;

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

        $wpdb->query( 'SET foreign_key_checks=0;' );
        $wpdb->query( Job::dropTableSql() );
        $wpdb->query( Impex::dropTableSql() );
        $wpdb->query( 'SET foreign_key_checks=1;' );
    }
}