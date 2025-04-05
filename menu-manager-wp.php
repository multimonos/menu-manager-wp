<?php
/**
 * Plugin Name: Menu Manager
 */

use MenuManager\Database\PostType\Menu;
use MenuManager\Plugin\Activate;
use MenuManager\Plugin\Deactivate;
use MenuManager\Wpcli\Commands\ImportCommands;
use MenuManager\Wpcli\Commands\JobCommands;
use MenuManager\Wpcli\Commands\MenuCommands;
use MenuManager\Wpcli\Commands\RootCommands;

require_once __DIR__ . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


register_activation_hook( __FILE__, [Activate::class, 'run'] );
register_deactivation_hook( __FILE__, [Deactivate::class, 'run'] );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    $command_namespace = 'mm';
    WP_CLI::add_command( $command_namespace, RootCommands::class );
    WP_CLI::add_command( $command_namespace . ' import', ImportCommands::class );
    WP_CLI::add_command( $command_namespace . ' jobs', JobCommands::class );
    WP_CLI::add_command( $command_namespace . ' menus', MenuCommands::class );
}

function menu_manager_plugin() {
    Menu::init();
}

menu_manager_plugin();