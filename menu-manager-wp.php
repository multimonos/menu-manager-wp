<?php
/**
 * Plugin Name: Menu Manager
 */

use MenuManager\Service\Plugin;
use MenuManager\Wpcli\Commands\BackupCommands;
use MenuManager\Wpcli\Commands\ImportCommands;
use MenuManager\Wpcli\Commands\JobCommands;
use MenuManager\Wpcli\Commands\MenuCommands;
use MenuManager\Wpcli\Commands\NodeCommands;
use MenuManager\Wpcli\Commands\RootCommands;

// guard
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// autoload
require_once __DIR__ . '/vendor/scoper-autoload.php';

// paths
define( 'MENU_MANAGER_URL', plugins_url( '', __FILE__ ) );
define( 'MENU_MANAGER_FILE', __FILE__ );

// wp cli'
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    $command_namespace = 'mm';
    WP_CLI::add_command( $command_namespace, RootCommands::class );
    WP_CLI::add_command( $command_namespace . ' job', JobCommands::class );
    WP_CLI::add_command( $command_namespace . ' menu', MenuCommands::class );
    WP_CLI::add_command( $command_namespace . ' node', NodeCommands::class );
    WP_CLI::add_command( $command_namespace . ' backup', BackupCommands::class );
}

// plugin
Plugin::load();
register_activation_hook( __FILE__, [Plugin::class, 'activate'] );
register_deactivation_hook( __FILE__, [Plugin::class, 'deactivate'] );

