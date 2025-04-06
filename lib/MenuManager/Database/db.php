<?php

namespace MenuManager\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

class db {
    protected static $capsule;

    public static function load(): Capsule {
        if ( is_null( self::$capsule ) ) {
            global $wpdb;

            // config
            $charset = self::wpCharset( 'utf8' );
            $collation = self::wpCollation( 'utf8_unicode_ci' );
            $config = [
                'driver'    => 'mysql',
                'host'      => DB_HOST,
                'database'  => DB_NAME,
                'username'  => DB_USER,
                'password'  => DB_PASSWORD,
                'charset'   => $charset,
                'collation' => $collation,
                'prefix'    => $wpdb->prefix,
            ];

            // conn
            $capsule = new Capsule();
            $capsule->addConnection( $config );
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            self::$capsule = $capsule;

        }

        return self::$capsule;
    }

    public static function wpCharset( string $default ): string {
        global $wpdb;
        preg_match( "/character\s+set\s+(.+?)\b/i", $wpdb->get_charset_collate(), $matches );
        return count( $matches ) === 2 ? $matches[1] : $default;
    }

    public static function wpCollation( string $default ): string {
        global $wpdb;
        preg_match( "/collate\s+(.+?)\b/i", $wpdb->get_charset_collate(), $matches );
        return count( $matches ) === 2 ? $matches[1] : $default;
    }
}