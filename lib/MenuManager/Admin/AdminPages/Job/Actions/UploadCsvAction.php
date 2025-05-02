<?php

namespace MenuManager\Admin\AdminPages\Job\Actions;

use MenuManager\Admin\Service\NoticeService;
use MenuManager\Admin\Types\AdminPostFormAction;
use MenuManager\Admin\Util\FormActionHelper;
use MenuManager\Model\Job;
use MenuManager\Tasks\Impex\LoadTask;

class UploadCsvAction implements AdminPostFormAction {

    public function id(): string {
        return 'mm_jobs_upload_csv';
    }

    public function name(): string {
        return __( 'Upload', 'menu-manager' );
    }

    public function register(): void {
        FormActionHelper::registerHandler( $this );
    }

    public function form(): string {
        ob_start();
        ?>
        <div class="card">
            <h2>Upload</h2>
            <p>Upload a CSV file to create a new job.</p>
            <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post" enctype="multipart/form-data" class="wp-upload-form">
                <label for="mm_impex_file" class="screen-reader-text">Select CSV File</label>
                <input type="file" id="mm_impex_file" name="mm_impex_file" class="file-upload" accept=".csv"/>
                <?php echo FormActionHelper::hiddenFields( $this ); ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle(): void {
        FormActionHelper::validateOrRedirect( $this, wp_get_referer() );

        // Check file upload
        if ( ! isset( $_FILES['mm_impex_file'] ) || $_FILES['mm_impex_file']['error'] !== UPLOAD_ERR_OK ) {
            NoticeService::errorRedirect( 'Error uploading CSV file', wp_get_referer() );
        }

        // Validate file type
        $file_type = wp_check_filetype( basename( $_FILES['mm_impex_file']['name'] ) );
        if ( $file_type['ext'] !== 'csv' ) {
            NoticeService::errorRedirect( 'File must be a csv', wp_get_referer() );
        }

        // Process the CSV file
        $csv_file = $_FILES['mm_impex_file']['tmp_name'];

        // Parse the CSV
        $task = new LoadTask();
        $rs = $task->run( $csv_file );

        if ( $rs->ok() ) {

            $job = $rs->getData()['job'] ?? null;

            if ( $job instanceof Job ) {
                // adjust the file ... maybe
                $job->source = $_FILES['mm_impex_file']['name'];
                if ( $job->save() ) {
                    NoticeService::successRedirect( sprintf( "Created job '%d' from '%s'.", $job->id, $job->source ), wp_get_referer() );
                }
            } else {
                NoticeService::successRedirect( $rs->getMessage(), wp_get_referer() );
            }

        }
        NoticeService::errorRedirect( $rs->getMessage(), wp_get_referer() );
    }
}