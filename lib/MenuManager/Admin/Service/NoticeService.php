<?php

namespace MenuManager\Admin\Service;

class NoticeService {

    // Message keys
    const SUCCESS = 'mmx_admin_notice_success';
    const ERROR = 'mmx_admin_notice_error';
    const WARNING = 'mmx_admin_notice_warning';

    public static function init(): void {
        add_action( 'admin_notices', [self::class, 'show'] );
        add_action( 'admin_notices', [self::class, 'add_javascript_notices'] );
    }

    public static function get( $key ): ?string {
        return get_transient( $key );
    }

    public static function set( string $key, string $message, ?string $redirectUrl = null ): void {
        set_transient( $key, $message, 30 );

        if ( ! empty( $redirectUrl ) ) {
            wp_redirect( $redirectUrl );
            exit;
        }
    }

    public static function success( string $message ): void {
        self::set( self::SUCCESS, $message );
    }

    public static function successRedirect( string $message, string $url = null ): void {
        self::set( self::SUCCESS, $message, $url );
    }

    public static function error( string $message ): void {
        self::set( self::ERROR, $message );
    }

    public static function errorRedirect( string $message, string $url = null ): void {
        self::set( self::ERROR, $message, $url );
    }

    public static function warning( string $message ): void {
        self::set( self::WARNING, $message );
    }

    public static function warningRedirect( string $message, string $url = null ): void {
        self::set( self::WARNING, $message, $url );
    }

    public static function show() {
        $keys = [
            self::ERROR   => 'notice-error',
            self::WARNING => 'notice-warning',
            self::SUCCESS => 'notice-success',
        ];

        foreach ( $keys as $key => $classname ) {
            if ( $message = self::get( $key ) ) {
                ?>
                <div class="notice <?php echo $classname; ?> is-dismissible">
                    <p><?php echo esc_html( $message ); ?></p>
                </div>
                <?php
                delete_transient( $key );
            }
        }
    }

    public static function add_javascript_notices() {
        $notices = [
            ['id' => 'menu-manager-error', 'type' => self::ERROR, 'class' => 'notice-error'],
            ['id' => 'menu-manager-warning', 'type' => self::WARNING, 'class' => 'notice-warning'],
            ['id' => 'menu-manager-success', 'type' => self::SUCCESS, 'class' => 'notice-success'],
        ];
//        foreach ( $notices as $notice ) {
        ?>
        <div id="mm-notice" class="notice is-dismissible hidden">
            <p class="mm-notice-message">{msg}</p>
        </div>
        <?php
//        }
        ?>
        <script>
        function mmNoticeShow( type, message ) {
            $notice = jQuery( '#mm-notice' )
            $clone = $notice.clone()
            $clone
                .removeClass( 'hidden notice-success notice-warning notice-error' )
                .addClass( type )
                .find( '.mm-notice-message' )
                .text( message )
            $clone.find( '.notice-dismiss' ).on( 'click', function () {jQuery( this ).closest( '.notice' ).slideUp( 200 );} )
            $notice.before( $clone )
            $clone.show()
        }

        window.addEventListener( 'mm-info', function ( e ) {
            mmNoticeShow( null, e?.detail?.message )
        } )
        window.addEventListener( 'mm-success', function ( e ) {
            mmNoticeShow( 'notice-success', e?.detail?.message )
        } )
        window.addEventListener( 'mm-warning', function ( e ) {
            mmNoticeShow( 'notice-warning', e?.detail?.message )
        } )
        window.addEventListener( 'mm-error', function ( e ) {
            mmNoticeShow( 'notice-error', e?.detail?.message )
        } )

        </script>
        <?php
    }
}