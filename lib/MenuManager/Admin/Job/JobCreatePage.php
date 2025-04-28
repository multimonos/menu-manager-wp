<?php

namespace MenuManager\Admin\Job;

use MenuManager\Admin\Service\NoticeService;
use MenuManager\Admin\Types\AdminPage;
use MenuManager\Model\Job;
use MenuManager\Model\Menu;
use MenuManager\Task\LoadTask;

class JobCreatePage implements AdminPage {
    public static function id(): string {
        return 'mm_job_create';
    }

    public static function init(): void {
        $svc = new self;

        add_action( 'admin_menu', [$svc, 'admin_menu_hook'] );
        add_action( 'admin_post_' . self::id(), [$svc, 'handle_impex_upload'] );
    }

    public function admin_menu_hook() {
        add_submenu_page(
            'edit.php?post_type=' . Menu::type(),
            'Upload',
            'Upload',
            'manage_options',
            self::id(),
            [$this, 'render']
        );
    }

    public function render() {
//        NoticeService::show();
        ?>
        <div class="wrap">
            <h1>Upload CSV</h1>

            <div class="card">
                <h2>Upload</h2>
                <p>Upload a CSV file to create a new job.</p>
                <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post" enctype="multipart/form-data" class="wp-upload-form">
                    <label for="mm_impex_file" class="screen-reader-text">Select CSV File</label>
                    <input type="file" id="mm_impex_file" name="mm_impex_file" class="file-upload" accept=".csv"/>
                    <input type="hidden" name="action" value="<?php echo self::id(); ?>"/>
                    <?php wp_nonce_field( self::id(), '_wpnonce' ); ?>
                    <?php submit_button( 'Upload', 'primary', 'create_job', false ); ?>
                </form>
            </div>
        </div>
        <?php
    }

    public function handle_impex_upload() {
//        exit;

        $redirect_url = admin_url( add_query_arg( [
            'post_type' => Menu::type(),
            'page'      => self::id(),
        ], 'edit.php' ) );

        if ( ! isset( $_POST['create_job'] ) ) {
            wp_redirect( $redirect_url );
            exit;
        }

        // Verify nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], self::id() ) ) {
            NoticeService::error( 'Security check failed.' );
            wp_redirect( $redirect_url );
            exit;
        }

        // Check file upload
        if ( ! isset( $_FILES['mm_impex_file'] ) || $_FILES['mm_impex_file']['error'] !== UPLOAD_ERR_OK ) {
            NoticeService::error( 'Error uploading CSV file' );
            wp_redirect( $redirect_url );
            exit;
        }

        // Validate file type
        $file_type = wp_check_filetype( basename( $_FILES['mm_impex_file']['name'] ) );
        if ( $file_type['ext'] !== 'csv' ) {
            NoticeService::error( 'File must be a csv' );
            wp_redirect( $redirect_url );
            exit;
        }

        // Process the CSV file
        $csv_file = $_FILES['mm_impex_file']['tmp_name'];

        // Parse the CSV
        $csv_data = array();
        $task = new LoadTask();
        $rs = $task->run( $csv_file );

        if ( $rs->ok() ) {
            $job = $rs->getData()['job'] ?? null;

            if ( $job instanceof Job ) {
                $job = $job->update( ['post_title' => sprintf( '#%s -- %s', $job->post->ID, $_FILES['mm_impex_file']['name'] )] );
                if ( $job instanceof Job ) {
                    NoticeService::success( sprintf( "Created job %s", $job->post->post_title ) );
                }
            } else {
                NoticeService::success( $rs->getMessage() );
            }

        } else {
            NoticeService::error( $rs->getMessage() );
        }

        wp_redirect( admin_url( add_query_arg( [
            'post_type' => Menu::type(),
            'page'      => self::id(),
        ], 'edit.php' ) ) );
    }
}