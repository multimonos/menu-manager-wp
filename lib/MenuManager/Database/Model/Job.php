<?php

namespace MenuManager\Database\Model;

class Job extends Model {

    const TABLE = 'mm_jobs';

    const STATUS_CREATED = 'created';
    const STATUS_RUNNING = 'running';
    const STATUS_DONE = 'done';

    public static function createTableSql(): string {
        global $wpdb;

        return 'CREATE TABLE ' . self::tablename() . " (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type ENUM('import','export'),
        status ENUM('created','running','done') NOT NULL DEFAULT 'created',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) " . $wpdb->get_charset_collate() . ';';
    }


    public static function createImport() {
        global $wpdb;

        return $wpdb->insert( self::tablename(), [
            'type'   => 'import',
            'status' => self::STATUS_CREATED,
        ] );
    }

    public static function canValidate( array $job ): bool {
        return $job['status'] === self::STATUS_CREATED;
    }


}