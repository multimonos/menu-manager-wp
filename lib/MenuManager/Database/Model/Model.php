<?php

namespace MenuManager\Database\Model;

class Model {

    const TABLE = 'unknown';

    public static function tablename(): string {
        global $wpdb;
        return $wpdb->prefix . static::TABLE;
    }

    public static function dropTableSql(): string {
        return 'DROP TABLE IF EXISTS ' . static::tablename() . ';';
    }

    public static function createTableSql(): string {
        return 'SELECT 1;';
    }

    public static function find( $id ): ?array {
        // retuns null if not found
        global $wpdb;
        return $wpdb->get_row( 'SELECT * FROM ' . static::tablename() . ' WHERE id=' . $id . ';', ARRAY_A );
    }

    public static function all(): array {
        global $wpdb;

        $rs = $wpdb->get_results( 'SELECT * FROM ' . static::tablename() . ';', ARRAY_A );

        if ( ! $rs ) {
            return [];
        }

        return $rs;
    }
}