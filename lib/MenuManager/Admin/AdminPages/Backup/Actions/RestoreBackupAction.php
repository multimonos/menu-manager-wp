<?php

namespace MenuManager\Admin\AdminPages\Backup\Actions;

use MenuManager\Admin\Types\AdminPostLinkAction;
use MenuManager\Admin\Util\AjaxActionHelper;
use MenuManager\Model\Backup;
use MenuManager\Model\Post;
use MenuManager\Tasks\Backup\RestoreBackupTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class RestoreBackupAction implements AdminPostLinkAction {
    public function id(): string {
        return 'mm_backup_restore';
    }

    public function name(): string {
        return __( 'Restore', 'menu-manager' );
    }

    public function register(): void {
        AjaxActionHelper::registerHandler( $this );
        AjaxActionHelper::registerFooterScript( $this );
    }

    public function link( Model|Post|\WP_Post $model ): string {
        return AjaxActionHelper::createLink( $this, $model );
    }

    public function handle(): void {
        // Validate
        AjaxActionHelper::validateOrFail( $this );

        // Get model.
        $backup = AjaxActionHelper::findOrFail( Backup::class );

        // Run
        $task = new RestoreBackupTask();
        $rs = $task->run( $backup->id );

        // Send result.
        AjaxActionHelper::sendResult( $rs, "Backup #{$backup->id} restored." );
    }

    public function script(): void {
        ?>
        <script id="js-<?php echo $this->id(); ?>">
        jQuery( function ( $ ) {

            const onSuccess = function ( res ) {
                window.dispatchEvent( new Event( 'mm-spinner-hide' ) )
                if ( res.success ) {
                    window.dispatchEvent( new CustomEvent( 'mm-success', { detail: { message: res.data.message } } ) )
                } else {
                    window.dispatchEvent( new CustomEvent( 'mm-error', { detail: { message: res.data.message } } ) )
                }
            }

            const onFailure = function ( e ) {
                console.error( 'err', { e } )
                window.dispatchEvent( new Event( 'mm-spinner-hide' ) )
                window.dispatchEvent( new CustomEvent( 'mm-error', { detail: { message: 'Failed yo' } } ) )
            }

            $( '.<?php echo AjaxActionHelper::linkClass( $this );?>' ).on( 'click', function ( e ) {
                e.preventDefault();

                if ( ! confirm( 'Are you sure ... this action cannot be undone?' ) ) {
                    return;
                }
                const payload = {
                    action: '<?php echo $this->id();?>',
                    post_id: $( this ).data( 'post-id' ),
                    _wpnonce: $( this ).data( 'nonce' )
                }

                $.post( '<?php echo admin_url( 'admin-ajax.php' ); ?>', payload, onSuccess ).fail( onFailure )
            } )
        } );
        </script>
        <?php
    }
}