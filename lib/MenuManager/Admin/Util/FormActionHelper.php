<?php

namespace MenuManager\Admin\Util;

use MenuManager\Admin\Service\NoticeService;
use MenuManager\Admin\Types\AdminPostAction;
use MenuManager\Admin\Types\AdminPostFormAction;
use MenuManager\Model\Post;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class FormActionHelper {

    public static function registerHandler( AdminPostAction $action ) {
        add_action( 'admin_post_' . $action->id(), [$action, 'handle'] );
    }

    public static function hiddenFields( AdminPostFormAction $action ): string {
        ob_start();
        ?>
        <input type="hidden" name="action" value="<?php echo $action->id(); ?>"/>
        <?php wp_nonce_field( $action->id(), '_wpnonce' ); ?>
        <?php submit_button( $action->name(), 'primary', 'submit', false ); ?>
        <?php
        return ob_get_clean();
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

    public static function validateOrRedirect( AdminPostAction $action, string $url ): void {
        // Permissions check.
        if ( ! current_user_can( 'manage_options' ) ) {
            NoticeService::errorRedirect( __( 'You do not have sufficient permissions to access this page.', 'menu-manager' ), $url );
        }

        // Submit button
        if ( ! isset( $_POST['submit'] ) ) {
            NoticeService::errorRedirect( 'Invalid form.', wp_get_referer() );
        }

        // Verify nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], $action->id() ) ) {
            NoticeService::errorRedirect( 'Security check failed.', $url );
        }
    }
}