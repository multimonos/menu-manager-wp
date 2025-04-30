<?php

namespace MenuManager\Service;

class Filesystem {

    public static function get(): \WP_Filesystem_Base {
        global $wp_filesystem;

        if ( ! function_exists( 'request_filesystem_credentials' ) ) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
        }

        if ( ! $wp_filesystem ) {
            \WP_Filesystem();
        }

        return $wp_filesystem;
    }

}