<?php

namespace MenuManager\Database\Model;

use stdClass;

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

//    public static function create( array $data = [] ): static {
//        $model = new static();
//        foreach ( $data as $k => $v ) {
//            if ( property_exists( $mode, $k ) ) {
//                $mode->$k = $v;
//            }
//        }
//        return $model;
//    }

    public static function find( $id ): ?stdClass {
        global $wpdb;
        // null if not found
        return $wpdb->get_row( 'SELECT * FROM ' . static::tablename() . ' WHERE id=' . $id . ';', OBJECT );
    }

    /**
     * @return stdClass[]
     */
    public static function all(): array {
        global $wpdb;

        $rs = $wpdb->get_results( 'SELECT * FROM ' . static::tablename() . ';', OBJECT );

        if ( ! $rs ) {
            return [];
        }

        return $rs;
    }
}