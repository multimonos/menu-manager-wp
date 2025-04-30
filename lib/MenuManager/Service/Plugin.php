<?php

namespace MenuManager\Service;

use MenuManager\Admin\AdminService;
use MenuManager\Model\Backup;
use MenuManager\Model\Impex;
use MenuManager\Model\Job;
use MenuManager\Model\Menu;
use MenuManager\Model\Node;
use MenuManager\Model\NodeMeta;

class Plugin {
    const CUSTOM_MODELS = [
        Impex::class,
        NodeMeta::class,
        Node::class,
        Backup::class,
    ];

    const POST_MODELS = [
        Job::class,
        Menu::class,
    ];

    public static function load(): void {
        // Initialize post models
        array_map(
            fn( $post_model ) => $post_model::init(),
            self::POST_MODELS
        );

        // Admin
        AdminService::init();
    }

    public static function activate() {
        Logger::taskInfo( 'plugin', 'activating...' );

        // Database deltas.
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Create database.
        $conn = Database::load()->getConnection();
        $conn->statement( 'SET foreign_key_checks=0;' );
        array_map(
            fn( $model ) => $model::createTable(),
            self::CUSTOM_MODELS
        );
        $conn->statement( 'SET foreign_key_checks=1;' );

        // Flush.
        flush_rewrite_rules();

        // Log.
        Logger::taskInfo( 'plugin', 'activated' );
    }

    public static function deactivate() {

        Logger::taskInfo( 'plugin', 'deactivating...' );

        // Clean posts.
        array_map(
            fn( $model ) => $model::dropTable(),
            self::POST_MODELS
        );

        // Clean custom data.
        $conn = Database::load()->getConnection();
        $conn->statement( 'SET foreign_key_checks=0;' );
        array_map(
            fn( $model ) => Database::load()::schema()->dropIfExists( $model::table() ),
            self::CUSTOM_MODELS
        );
        $conn->statement( 'SET foreign_key_checks=1;' );

        // Flush
        flush_rewrite_rules();

        // Log
        Logger::taskInfo( 'plugin', 'deactivated' );
    }
}