<?php

namespace MenuManager\Plugin;

use MenuManager\Database\db;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Job;
use MenuManager\Database\Model\Node;
use MenuManager\Database\Model\NodeMeta;

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
        db::load()->getConnection()->statement( 'SET foreign_key_checks=0;' );
        Impex::createTable();
        Job::createTable();
        NodeMeta::createTable();
        Node::createTable();
        db::load()->getConnection()->statement( 'SET foreign_key_checks=1;' );
    }
}