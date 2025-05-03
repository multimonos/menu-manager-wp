<?php

namespace MenuManager\Service;

class Filesystem {

    const BASEDIR = 'menu-manager';

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

    public static function open( string $path, string $mode ) {
        self::assert();
        return fopen( $path, $mode );
    }

    public static function pathFor( ?string $filename = "" ): string {
        return trailingslashit( wp_upload_dir()['basedir'] . '/' . self::BASEDIR ) . $filename;
    }

    public static function secureFilename( string $ext, string $prefix = '' ): string {
        /* generate a secure filename */
        return $prefix . wp_generate_uuid4() . $ext;
    }

    public static function assert(): void {
        /* ensure file system paths exist */
        $fs = self::get();

        // maybe create basedir
        $basedir = self::pathFor( null );
        if ( ! $fs->exists( $basedir ) ) {
            $fs->mkdir( $basedir );
            Logger::info( 'created directory ' . $basedir );
        }
    }


}