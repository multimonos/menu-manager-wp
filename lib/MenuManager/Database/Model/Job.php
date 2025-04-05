<?php

namespace MenuManager\Database\Model;

class Job extends Model {

    const TABLE = 'mm_jobs';

    public static function createTableSql(): string {
        global $wpdb;

        return 'CREATE TABLE ' . self::tablename() . ' (
        id int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(32),
        status VARCHAR(32),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ' . $wpdb->get_charset_collate() . ';';
    }


    public static function createImport() {
        global $wpdb;

        return $wpdb->insert( self::tablename(), [
            'type'   => 'import',
            'status' => 'created',
        ] );
    }


}