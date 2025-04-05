<?php

namespace MenuManager\Database;

class DB {
    public static function startTransaction() {
        global $wpdb;
        $wpdb->query( 'START TRANSACTION;' );
    }

    public static function rollback() {
        global $wpdb;
        $wpdb->query( 'ROLLBACK;' );
    }

    public static function commit() {
        global $wpdb;
        $wpdb->query( 'COMMIT;' );
    }

}