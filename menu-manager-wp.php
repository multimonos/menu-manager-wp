<?php
/**
 * Plugin Name: Menu Manager
 */

use MenuManager\Database\PostType\MenuPost;
use MenuManager\Plugin\Activate;
use MenuManager\Plugin\Deactivate;
use MenuManager\Wpcli\Commands\ImportCommands;
use MenuManager\Wpcli\Commands\JobCommands;
use MenuManager\Wpcli\Commands\MenuCommands;
use MenuManager\Wpcli\Commands\RootCommands;

require_once __DIR__ . '/vendor/scoper-autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// wp cli'
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    // db::load();
    $command_namespace = 'mm';
    WP_CLI::add_command( $command_namespace, RootCommands::class );
    WP_CLI::add_command( $command_namespace . ' import', ImportCommands::class );
    WP_CLI::add_command( $command_namespace . ' job', JobCommands::class );
    WP_CLI::add_command( $command_namespace . ' menu', MenuCommands::class );
}

// plugin
register_activation_hook( __FILE__, [Activate::class, 'run'] );
register_deactivation_hook( __FILE__, [Deactivate::class, 'run'] );

function menu_manager_plugin() {
    MenuPost::init();
}

menu_manager_plugin();