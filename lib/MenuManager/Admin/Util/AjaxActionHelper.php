<?php

namespace MenuManager\Admin\Util;

use MenuManager\Admin\Types\AdminAction;
use MenuManager\Model\Post;
use MenuManager\Service\Database;
use MenuManager\Tasks\TaskResult;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class AjaxActionHelper {
    public static function registerHandler( AdminAction $action ) {
        add_action( 'wp_ajax_' . $action->id(), [$action, 'handle'] );
    }

    public static function registerFooterScript( AdminAction $action ) {
        add_action( 'admin_footer', [$action, 'script'] );
    }

    public static function linkClass( AdminAction $action ): string {
        return $action->id() . '-link';
    }

    public static function createLink( AdminAction $action, Model|Post|\WP_Post $model ): string {
        return sprintf(
            '<a href="#" class="%s" data-post-id="%d" data-nonce="%s">%s</a>',
            self::linkClass( $action ),
            ActionHelper::modelId( $model ),
            esc_attr( ActionHelper::modelNonce( $action, $model ) ),
            $action->name()
        );
    }

    public static function validateOrFail( AdminAction $action, bool $debug = false ): void {
        if ( $debug ) {
            error_log( print_r( [
                'action_id' => $action->id(),
                'post_id'   => $_POST['post_id'] ?? null,
                '_wpnonce'  => $_POST['_wpnonce'] ?? null,
            ], true ) );
        }
        // Permissions check
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( ['message' => __( 'You do not have sufficient permission to perform this action.', 'menu-manager' )] );
        }

        // Required params.
        if ( ! isset( $_POST['post_id'] ) ) {
            wp_send_json_error( ['message' => __( "Missing required parameter 'post_id'.", 'menu-manager' )] );
        }

        if ( ! isset( $_POST['_wpnonce'] ) ) {
            wp_send_json_error( ['message' => __( "Missing nonce.", 'menu-manager' )] );
        }

        // Validate params.
        if ( ! is_numeric( $_POST['post_id'] ) ) {
            wp_send_json_error( ['message' => __( "Required parameter 'post_id' must be numeric.", 'menu-manager' )] );
        }

        if ( intval( $_POST['post_id'] ) <= 0 ) {
            wp_send_json_error( ['message' => __( "Required parameter 'post_id' invalid.", 'menu-manager' )] );
        }

        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], ActionHelper::nonceSalt( $action->id(), $_POST['post_id'] ) ) ) {
            wp_send_json_error( ['message' => __( 'Security check failed.', 'menu-manager' )] );
        }
    }

    public static function findOrFail( string $model_class ): ?Model {
        Database::load();

        $id = intval( $_POST['post_id'] );

        $model = $model_class::find( $id );

        if ( $model === null ) {
            wp_send_json_error( ['message' => "Record #{$model_id} not found."] );
        }

        return $model;
    }

    public static function findPostOrFail( string $model_class ): ?Post {
        $id = intval( $_POST['post_id'] );

        $model = $model_class::find( $id );

        if ( $model === null ) {
            wp_send_json_error( ['message' => "Record #{$model_id} not found."] );
        }

        return $model;
    }

    public static function sendResult( TaskResult $result, string $success_message = '' ): void {
        if ( ! $result->ok() ) {
            wp_send_json_error( ['message' => $result->getMessage()] );
        } else {
            $msg = empty( $success_message ) ? $result->getMessage() : $success_message;
            wp_send_json_success( ['message' => $msg] );
        }
    }


    public static function script( AdminAction $action, bool $confirm = false ): void {
        /* default workflow that blocks ui while ajax happens and optionally requires user to confirm request */
        ?>
        <script id="js-<?php echo $action->id(); ?>">
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

            $( '.<?php echo AjaxActionHelper::linkClass( $action );?>' ).on( 'click', function ( e ) {
                e.preventDefault();

                <?php if($confirm):?>
                if ( ! confirm( 'Are you sure ... this action cannot be undone?' ) ) {
                    return;
                }
                <?php endif; ?>

                const payload = {
                    action: '<?php echo $action->id();?>',
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