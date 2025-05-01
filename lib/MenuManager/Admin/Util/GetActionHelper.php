<?php

namespace MenuManager\Admin\Util;

use MenuManager\Admin\Service\UserInterface\NoticeService;
use MenuManager\Admin\Types\AdminPostAction;
use MenuManager\Model\Post;
use MenuManager\Service\Database;
use MenuManager\Task\TaskResult;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class GetActionHelper {

    public static function registerHandler( AdminPostAction $action ) {
        add_action( 'admin_post_' . $action->id(), [$action, 'handle'] );
    }

    public static function createLink( AdminPostAction $action, Model|Post|\WP_Post $model, bool $confirm = false ): string {
        $args = [
            'action'   => $action->id(),
            'post_id'  => ActionHelper::modelId( $model ),
            '_wpnonce' => ActionHelper::modelNonce( $action, $model ),
        ];

        $url = admin_url( add_query_arg( $args, 'admin-post.php' ) );

        if ( $confirm ) {
            $link = sprintf(
                '<a href="%s" onclick="return confirm(\'%s\')">%s</a>',
                $url,
                __( 'Are you sure?  This action cannot be undone ...', 'menu-manager' ),
                $action->name()
            );
        } else {
            $link = sprintf( '<a href="%s">%s</a>', $url, $action->name() );

        }

        return $link;
    }

    public static function validateOrFail( AdminPostAction $action ): void {
        // Permissions check.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'menu-manager' ) );
        }

        // Parameters exist.
        if ( ! isset( $_GET['post_id'] ) ) {
            wp_die( __( "Missing required parameter 'post_id'.", 'menu-manager' ) );
        }

        if ( ! $_GET['_wpnonce'] ) {
            wp_die( __( "Missing nonce.", 'menu-manager' ) );
        }

        // Validate params.
        if ( ! is_numeric( $_GET['post_id'] ) ) {
            wp_die( __( "Required parameter 'post_id' must be numeric.", 'menu-manager' ) );
        }

        if ( intval( $_GET['post_id'] ) <= 0 ) {
            wp_die( __( "Required parameter 'post_id' invalid.", 'menu-manager' ) );
        }

        if ( ! wp_verify_nonce( $_GET['_wpnonce'], ActionHelper::nonceSalt( $action->id(), $_GET['post_id'] ) ) ) {
            wp_die( __( 'Security check failed.', 'menu-manager' ) );
        }
    }

    public static function findOrRedirect( string $model_class ): ?Model {
        Database::load();

        $id = intval( $_GET['post_id'] );

        $model = $model_class::find( $id );

        if ( $model === null ) {
            NoticeService::errorRedirect( "Record #{$id} not found.", wp_get_referer() );
        }

        return $model;
    }

    public static function sendResult( TaskResult $result, string $success_message = '' ): void {
        if ( ! $result->ok() ) {
            NoticeService::errorRedirect( $result->getMessage(), wp_get_referer() );
        } else {
            $msg = empty( $success_message ) ? $result->getMessage() : $success_message;
            NoticeService::successRedirect( $msg, wp_get_referer() );
        }
    }
}