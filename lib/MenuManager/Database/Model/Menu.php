<?php

namespace MenuManager\Database\Model;

class Menu extends Model {
    const TABLE = 'mm_menus';

    public static function createTableSql(): string {
        global $wpdb;

        return 'CREATE TABLE ' . self::tablename() . ' (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        menu_post_id BIGINT UNSIGNED NOT NULL,
        page VARCHAR(32),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY idx_menu_post_id(menu_post_id),
        CONSTRAINT fk_menu_post_id FOREIGN KEY (menu_post_id) REFERENCES wp_posts (ID) ON DELETE CASCADE
        ) ' . $wpdb->get_charset_collate() . ';';
    }

}