<?php

namespace MenuManager\Admin\Service;

class NoticeService {

    // Message keys
    const SUCCESS = 'mmx_admin_notice_success';
    const ERROR = 'mmx_admin_notice_error';
    const WARNING = 'mmx_admin_notice_warning';

    public static function success( string $message ): void {
        set_transient( self::SUCCESS, $message, 30 );
    }

    public static function error( string $message ): void {
        set_transient( self::ERROR, $message, 30 );
    }

    public static function warning( string $message ): void {
        set_transient( self::WARNING, $message, 30 );
    }

    public static function get( $key ): ?string {
        return get_transient( $key );
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