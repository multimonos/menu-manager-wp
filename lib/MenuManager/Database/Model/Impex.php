<?php

namespace MenuManager\Database\Model;

class Impex extends Model {

    const TABLE = 'mm_impex';

    public static function createTableSql(): string {
        global $wpdb;

//        custom_name VARCHAR(32),
//        custom_value VARCHAR(1000),

        return 'CREATE TABLE ' . self::tablename() . ' (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        job_id INT UNSIGNED,
        action VARCHAR(32),
        menu VARCHAR(32),
        page VARCHAR(32),
        batch_id VARCHAR(32),
        type VARCHAR(32),
        item_id MEDIUMINT UNSIGNED, 
        title VARCHAR(255),
        prices VARCHAR(100),
        image_ids VARCHAR(100),
        is_new BOOLEAN NOT NULL DEFAULT 0,
        is_glutensmart BOOLEAN NOT NULL DEFAULT 0,
        is_organic BOOLEAN NOT NULL DEFAULT 0,
        is_vegan BOOLEAN NOT NULL DEFAULT 0,
        is_vegetarian BOOLEAN NOT NULL DEFAULT 0,
        description VARCHAR(1000),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY idx_item_id (item_id),
        KEY idx_batch_id (batch_id),
        CONSTRAINT fk_job_id FOREIGN KEY (job_id) REFERENCES ' . Job::tablename() . '(id) ON DELETE CASCADE
        ) ' . $wpdb->get_charset_collate() . ';';
    }

}