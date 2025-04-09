<?php

namespace MenuManager\Plugin;

use MenuManager\Database\db;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Job;
use MenuManager\Database\Model\Node;
use MenuManager\Database\Model\NodeMeta;

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
        db::load()->getConnection()->statement( 'SET foreign_key_checks=0;' );
        db::load()::schema()->dropIfExists( Impex::TABLE );
        db::load()::schema()->dropIfExists( Job::TABLE );
        db::load()::schema()->dropIfExists( NodeMeta::TABLE );
        db::load()::schema()->dropIfExists( Node::TABLE );
        db::load()->getConnection()->statement( 'SET foreign_key_checks=1;' );
    }
}