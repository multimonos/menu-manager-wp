<?php

namespace MenuManager\Admin\Service;

class NoticeService {

    // Message keys
    const SUCCESS = 'mmx_admin_notice_success';
    const ERROR = 'mmx_admin_notice_error';
    const WARNING = 'mmx_admin_notice_warning';

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

    public static function successRedirect( string $message, string $url ): void {
        self::set( self::SUCCESS, $message, $url );
    }

    public static function error( string $message ): void {
        self::set( self::ERROR, $message );
    }

    public static function errorRedirect( string $message, string $url ): void {
        self::set( self::ERROR, $message, $url );
    }

    public static function warning( string $message ): void {
        self::set( self::WARNING, $message );
    }

    public static function warningRedirect( string $message, $url ): void {
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
}