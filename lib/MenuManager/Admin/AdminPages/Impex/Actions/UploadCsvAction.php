<?php

namespace MenuManager\Admin\AdminPages\Impex\Actions;

use MenuManager\Admin\Service\NoticeService;
use MenuManager\Admin\Types\AdminPostFormAction;
use MenuManager\Model\Job;
use MenuManager\Tasks\Impex\LoadTask;

class UploadCsvAction implements AdminPostFormAction {

    public string $redirectUrl;

    public function id(): string {
        return 'mm_upload';
    }

    public function name(): string {
        return __( 'Upload', 'menu-manager' );
    }


    public function register(): void {
        add_action( 'admin_post_' . $this->id(), [$this, 'handle'] );
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
                <input type="hidden" name="action" value="<?php echo $this->id(); ?>"/>
                <?php wp_nonce_field( $this->id(), '_wpnonce' ); ?>
                <?php submit_button( 'Upload', 'primary', 'create_job', false ); ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle(): void {
        if ( ! isset( $_POST['create_job'] ) ) {
            NoticeService::errorRedirect( 'Invalid form.', $this->redirectUrl );
        }

        // Verify nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], $this->id() ) ) {
            NoticeService::errorRedirect( 'Security check failed.', $this->redirectUrl );
        }

        // Check file upload
        if ( ! isset( $_FILES['mm_impex_file'] ) || $_FILES['mm_impex_file']['error'] !== UPLOAD_ERR_OK ) {
            NoticeService::errorRedirect( 'Error uploading CSV file', $this->redirectUrl );
        }

        // Validate file type
        $file_type = wp_check_filetype( basename( $_FILES['mm_impex_file']['name'] ) );
        if ( $file_type['ext'] !== 'csv' ) {
            NoticeService::errorRedirect( 'File must be a csv', $this->redirectUrl );
        }

        // Process the CSV file
        $csv_file = $_FILES['mm_impex_file']['tmp_name'];

        // Parse the CSV
        $task = new LoadTask();
        $rs = $task->run( $csv_file );

        if ( $rs->ok() ) {
            $job = $rs->getData()['job'] ?? null;

            if ( $job instanceof Job ) {
                // adjust the title
                $job = $job->update( ['post_title' => sprintf( '#%s -- %s', $job->post->ID, $_FILES['mm_impex_file']['name'] )] );
                if ( $job instanceof Job ) {
                    NoticeService::successRedirect( sprintf( "Created job %s", $job->post->post_title ), $this->redirectUrl );
                }
            } else {
                NoticeService::successRedirect( $rs->getMessage(), $this->redirectUrl );
            }

        }
        NoticeService::errorRedirect( $rs->getMessage(), $this->redirectUrl );
    }
}