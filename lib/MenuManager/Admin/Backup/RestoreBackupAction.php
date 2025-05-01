<?php

namespace MenuManager\Admin\Backup;

use MenuManager\Admin\Types\AdminModelLinkAction;
use MenuManager\Model\Backup;
use MenuManager\Service\Database;
use MenuManager\Task\RestoreTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class RestoreBackupAction implements AdminModelLinkAction {
    public function id(): string {
        return 'mm_backup_restore';
    }

    public function register(): void {
        add_action( 'wp_ajax_' . $this->id(), [$this, 'handle'] );
        add_action( 'admin_footer', [$this, 'script'] );
    }


    public function link( Model $model ): string {
        $nonce = wp_create_nonce( $this->id() . '_' . $model->id );

        return sprintf(
            '<a href="#" class="restore-backup-action-link" data-post-id="%d" data-nonce="%s">Restore</a>',
            $model->id,
            esc_attr( $nonce )
        );
    }

    public function handle(): void {
        Database::load();

        // Check if user is allowed
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( ['message' => 'You do not have sufficient permission to perform this action.'] );
        }
        // Verify parameters
        if ( ! isset( $_POST['post_id'] ) || ! isset( $_POST['_wpnonce'] ) ) {
            wp_send_json_error( ['message' => 'Missing required parameters.'] );
        }
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], $this->id() . '_' . $_POST['post_id'] ) ) {
            wp_send_json_error( ['message' => 'Security check failed.'] );
        }

        // Get the job
        $post_id = intval( $_REQUEST['post_id'] );
        $backup = Backup::find( $post_id );
        if ( $backup === null ) {
            wp_send_json_error( ['message' => "Backup #{$post_id} not found."] );
        }

        // run
        $task = new RestoreTask();
        $rs = $task->run( $backup->id );

        if ( ! $rs->ok() ) {
            wp_send_json_error( ['message' => $rs->getMessage()] );

        } else {
            wp_send_json_success( ['message' => "Restore backup #{$backup->id} success!"] );
        }
//        $task = new JobRunTask();
//        $task->run( $job->id );
//        wp_send_json_success( ['message' => "Job #{$job->id} complete."] );
    }

    public function script(): void {
        ?>
        <script id="js-<?php echo $this->id(); ?>">
        jQuery( function ( $ ) {
            console.log( 'i was added', jQuery )
            const onSuccess = function ( res ) {
                console.log( 'success', { res } )
                window.dispatchEvent( new Event( 'mm-spinner-hide' ) )
                if ( res.success ) {
                    window.dispatchEvent( new CustomEvent( 'mm-success', { detail: { message: res.data.message } } ) )
                } else {
                    window.dispatchEvent( new CustomEvent( 'mm-error', { detail: { message: res.data.message } } ) )
                }
            }

            const onFailure = function ( e ) {
                console.log( 'err', { e } )
                window.dispatchEvent( new Event( 'mm-spinner-hide' ) )
                window.dispatchEvent( new CustomEvent( 'mm-error', { detail: { message: 'Failed yo' } } ) )
            }

            $( '.restore-backup-action-link' ).on( 'click', function ( e ) {
                const payload = {
                    action: '<?php echo $this->id();?>',
                    post_id: $( this ).data( 'post-id' ),
                    _wpnonce: $( this ).data( 'nonce' )
                }
                console.log( { payload } )

                $.post( '<?php echo admin_url( 'admin-ajax.php' ); ?>', payload, onSuccess ).fail( onFailure )

            } )
        } );
        </script>
        <?php
    }
}