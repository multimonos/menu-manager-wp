<?php

namespace MenuManager\Database\Model;

class Model {

    const TABLE = 'unknown';

    protected array $data = [];

    public function __construct( array $data = [] ) {
        foreach ( $data as $field => $value ) {
            $this->__set( $field, $value );
        }
    }

    public function __get( string $field ) {
        if ( ! static::isField( $field ) ) {
            throw new \Exception( "Unknown field '{$field}' on " . static::class );
        }
        return $this->castOut( $field, $this->data[$field] ) ?? null;
    }

    public function __set( string $field, $value ) {
        if ( ! static::isField( $field ) ) {
            throw new \Exception( "Unknown field '{$field}' on " . static::class );
        }
        $this->data[$field] = $this->castIn( $field, $value );
    }

    protected function castIn( string $field, $value ) {
        $type = static::$fields[$field] ?? null;

        return match ($type) {
            'int' => (int)$value,
            'string' => (string)$value,
            'bool' => (bool)$value,
            'float' => (float)$value,
            default => $value
        };
    }

    protected function castOut( string $field, $value ) {
        return $value;
    }

    protected function toArray() {
        return $this->data;
    }

    public static function isField( $field ): bool {
        return isset( static::$fields[$field] );
    }

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

    public static function create( array $data = [] ): static {
        return new static( $data );
    }

    public static function find( $id ): ?static {
        global $wpdb;

        // null if not found
        $row = $wpdb->get_row( 'SELECT * FROM ' . static::tablename() . ' WHERE id=' . $id . ';', ARRAY_A );

        if ( is_array( $row ) ) {
            return static::create( $row );
        }

        return null;
    }

    /**
     * @return static[]
     */
    public static function all(): array {
        global $wpdb;

        $rs = $wpdb->get_results( 'SELECT * FROM ' . static::tablename() . ';', ARRAY_A );

        if ( ! $rs ) {
            return [];
        }

        return array_map( fn( $arr ) => static::create( $arr ), $rs );
    }
}