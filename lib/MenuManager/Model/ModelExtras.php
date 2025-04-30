<?php

namespace MenuManager\Model;

trait  ModelExtras {
    public static function table(): string {
        return (new static)->getTable();
    }

    public static function wptable(): string {
        global $wpdb;
        return $wpdb->prefix . static::table();
    }

    public function attributesForInsert() {
        return $this->getAttributesForInsert();
    }
}