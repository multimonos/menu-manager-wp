<?php

namespace MenuManager\Admin\Service\UserInterface;

class SpinnerService {

    public static function id(): string {
        return 'menu-manager-block-ui';
    }

    public static function init(): void {
        add_action( 'admin_head', function () {
            ?>
            <div id="<?php echo self::id(); ?>" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:#fff8; z-index:9999; text-align:center; padding-top:20%;">
                <span class="spinner is-active" style="float:none;"></span>
            </div>
            <script>
            window.addEventListener( 'mm-spinner-show', function () {
                jQuery( '#menu-manager-block-ui' ).show();
            } )
            window.addEventListener( 'mm-spinner-hide', function () {
                jQuery( '#menu-manager-block-ui' ).hide();
            } )
            </script>
            <?php
        } );

    }
}