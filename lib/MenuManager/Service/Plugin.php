<?php

namespace MenuManager\Service;

use MenuManager\Model\Impex;
use MenuManager\Model\Job;
use MenuManager\Model\Node;
use MenuManager\Model\NodeMeta;

class Plugin {
    public static function activate() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        self::createDatabase();
        flush_rewrite_rules();
        Logger::taskInfo( 'plugin', 'activated' );
    }

    public static function deactivate() {
        self::dropDatabase();
        flush_rewrite_rules();
        Logger::taskInfo( 'plugin', 'deactivated' );
    }

    public static function createDatabase() {
        $conn = Database::load()->getConnection();
        $conn->statement( 'SET foreign_key_checks=0;' );
        Impex::createTable();
        Job::createTable();
        NodeMeta::createTable();
        Node::createTable();
        $conn->statement( 'SET foreign_key_checks=1;' );
    }


    public static function dropDatabase() {

        $tables = [
            Impex::TABLE,
            Job::TABLE,
            NodeMeta::TABLE,
            Node::TABLE,
        ];

        // drop
        $conn = Database::load()->getConnection();
        $conn->statement( 'SET foreign_key_checks=0;' );

        foreach ( $tables as $table ) {
            Database::load()::schema()->dropIfExists( $table );
        }

        $conn->statement( 'SET foreign_key_checks=1;' );
    }
}