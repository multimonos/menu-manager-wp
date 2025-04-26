<?php

use MenuManager\Task\ExportExcelTask;
use MenuManager\Task\LoadTask;
use MenuManager\Types\ExportMethod;

function my_plugin_admin_page() {
    ?>
    <div class="wrap">
        <h1>CSV Upload for My Plugin</h1>

        <?php
        // Show any error/success messages
        settings_errors( 'my_plugin_messages' );
        ?>

        <div class="card">
            <h2>Upload</h2>
            <p>Upload a CSV file to create a new job.</p>
            <form method="post" enctype="multipart/form-data" class="wp-upload-form">
                <?php wp_nonce_field( 'my_plugin_csv_upload', 'my_plugin_nonce' ); ?>
                <label for="my_plugin_csv_file" class="screen-reader-text">Select CSV File</label>
                <input type="file" id="my_plugin_csv_file" name="my_plugin_csv_file" class="file-upload" accept=".csv"/>
                <?php submit_button( 'Upload', 'primary', 'my_plugin_submit', false ); ?>
            </form>
        </div>
    </div>
    <?php
}

function my_plugin_handle_csv_upload() {
    if ( ! isset( $_POST['my_plugin_submit'] ) ) {
        return;
    }

    // Verify nonce
    if ( ! isset( $_POST['my_plugin_nonce'] ) || ! wp_verify_nonce( $_POST['my_plugin_nonce'], 'my_plugin_csv_upload' ) ) {
        add_settings_error( 'my_plugin_messages', 'nonce_error', 'Security check failed.', 'error' );
        return;
    }

    // Check file upload
    if ( ! isset( $_FILES['my_plugin_csv_file'] ) || $_FILES['my_plugin_csv_file']['error'] !== UPLOAD_ERR_OK ) {
        add_settings_error( 'my_plugin_messages', 'upload_error', 'Error uploading CSV file.', 'error' );
        return;
    }

    // Validate file type
    $file_type = wp_check_filetype( basename( $_FILES['my_plugin_csv_file']['name'] ) );
    if ( $file_type['ext'] !== 'csv' ) {
        add_settings_error( 'my_plugin_messages', 'wrong_filetype', 'Only CSV files are allowed.', 'error' );
        return;
    }

    // Process the CSV file
    $csv_file = $_FILES['my_plugin_csv_file']['tmp_name'];

    // Parse the CSV
    $csv_data = array();
    $task = new LoadTask();
    $rs = $task->run( $csv_file );

    if ( ! $rs->ok() ) {
        add_settings_error( 'my_plugin_messages', 'parse_error', $rs->getMessage(), 'error' );
    } else {
        add_settings_error( 'my_plugin_messages', 'file_success', $rs->getMessage(), 'success' );
    }
}

add_action( 'admin_init', 'my_plugin_handle_csv_upload' );

// Register your admin page
function my_plugin_add_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=menus',
        'Upload',
        'Upload',
        'manage_options',
        'my-plugin-import',
        'my_plugin_admin_page',
    );
}

add_action( 'admin_menu', 'my_plugin_add_admin_menu' );


/**
 * Add an export column to the menus list table
 */
//function add_export_column_to_menus( $columns ) {
//    $columns['export_csv'] = __( 'Export', 'menu-manager' );
//    $columns['export_excel'] = __( 'Export', 'menu-manager' );
//    return $columns;
//}
//
//add_filter( 'manage_menus_posts_columns', 'add_export_column_to_menus' );


/**
 * Add Export CSV action to the menus CPT list
 */
//add_filter( 'post_row_actions', function ( $actions, $post ) {
//    // Only apply to our 'menus' post type
//    if ( $post->post_type === 'menus' ) {
//        // export csv
//        $nonce = wp_create_nonce( 'export_menu_csv_' . $post->ID );
//        $actions['export_csv'] = sprintf(
//            '<a href="%s" aria-label="%s">%s</a>',
//            admin_url( sprintf( 'admin-post.php?action=export_menu_csv&menu_id=%s&_wpnonce=%s', $post->ID, $nonce ) ),
//            esc_attr( sprintf( __( 'Export "%s" to CSV', 'menu-manager' ), $post->post_title ) ),
//            __( 'Export CSV', 'menu-manager' )
//        );
//        // export excel
//        $nonce = wp_create_nonce( 'export_menu_excel_' . $post->ID );
//        $actions['export_excel'] = sprintf(
//            '<a href="%s" aria-label="%s">%s</a>',
//            admin_url( sprintf( 'admin-post.php?action=export_menu_excel&menu_id=%s&_wpnonce=%s', $post->ID, $nonce ) ),
//            esc_attr( sprintf( __( 'Export "%s" to Excel', 'menu-manager' ), $post->post_title ) ),
//            __( 'Export Excel', 'menu-manager' )
//        );
//    }
//
//    return $actions;
//}, 10, 9999 );


/**
 * Handle export menu to CSV action
 */

//add_action( 'admin_post_export_menu_csv', function () {
//    // Check if user is allowed
//    if ( ! current_user_can( 'manage_options' ) ) {
//        wp_die( __( 'You do not have sufficient permissions to access this page.', 'menu-manager' ) );
//    }
//
//    // Verify parameters
//    if ( ! isset( $_GET['menu_id'] ) || ! isset( $_GET['_wpnonce'] ) ) {
//        wp_die( __( 'Missing required parameters.', 'menu-manager' ) );
//    }
//
//    $menu_id = intval( $_GET['menu_id'] );
//
//    // Verify nonce
//    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'export_menu_csv_' . $menu_id ) ) {
//        wp_die( __( 'Security check failed.', 'menu-manager' ) );
//    }
//
//    // Get the menu
//    $menu = get_post( $menu_id );
//    if ( ! $menu || $menu->post_type !== 'menus' ) {
//        wp_die( __( 'Menu not found.', 'menu-manager' ) );
//    }
//
//    $path = "menu-export_{$menu->post_name}_{$menu->ID}__" . date( 'Ymd\THis' ) . '.csv';
//    $task = new ExportCsvTask();
//    $rs = $task->run( ExportMethod::Download, $menu, $path );
//    exit;
//} );

add_action( 'admin_post_export_menu_excel', function () {
    // Check if user is allowed
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'menu-manager' ) );
    }

    // Verify parameters
    if ( ! isset( $_GET['menu_id'] ) || ! isset( $_GET['_wpnonce'] ) ) {
        wp_die( __( 'Missing required parameters.', 'menu-manager' ) );
    }

    $menu_id = intval( $_GET['menu_id'] );

    // Verify nonce
    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'export_menu_excel_' . $menu_id ) ) {
        wp_die( __( 'Security check failed.', 'menu-manager' ) );
    }

    // Get the menu
    $menu = get_post( $menu_id );
    if ( ! $menu || $menu->post_type !== 'menus' ) {
        wp_die( __( 'Menu not found.', 'menu-manager' ) );
    }

    $path = "menu-export_{$menu->post_name}_{$menu->ID}__" . date( 'Ymd\THis' ) . '.xlsx';
    $task = new ExportExcelTask();
    $rs = $task->run( ExportMethod::Download, $menu, $path );
    exit;
} );