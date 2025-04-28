<?php

use MenuManager\Task\LoadTask;

//function my_plugin_admin_page() {
//    ?>
    <!--    <div class="wrap">-->
    <!--        <h1>CSV Upload for My Plugin</h1>-->
    <!---->
    <!--        <div class="card">-->
    <!--            <h2>Upload</h2>-->
    <!--            <p>Upload a CSV file to create a new job.</p>-->
    <!--            <form method="post" enctype="multipart/form-data" class="wp-upload-form">-->
    <!--                --><?php //wp_nonce_field( 'my_plugin_csv_upload', 'my_plugin_nonce' ); ?>
    <!--                <label for="my_plugin_csv_file" class="screen-reader-text">Select CSV File</label>-->
    <!--                <input type="file" id="my_plugin_csv_file" name="my_plugin_csv_file" class="file-upload" accept=".csv"/>-->
    <!--                --><?php //submit_button( 'Upload', 'primary', 'my_plugin_submit', false ); ?>
    <!--            </form>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--    --><?php
//}

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

//add_action( 'admin_init', 'my_plugin_handle_csv_upload' );
