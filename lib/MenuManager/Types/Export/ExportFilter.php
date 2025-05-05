<?php

namespace MenuManager\Types\Export;

class ExportFilter {
    public string $fn;
    public string $field;
    public mixed $values;

    public static function make( string $fn, string $field, array $values ) {
        $x = new self();
        $x->field = $field;
        $x->values = $values;
        $x->fn = $fn;
        return $x;
    }

    public function include( mixed $field_value ): bool {
        switch ( $this->fn ) {
            case 'in_array':
                return in_array( $field_value, $this->values );
                break;

            case 'csv_in_array':
                /* source field is a csv */
                $needles = preg_split( '/\s*,\s*/', $field_value );
                foreach ( $needles as $needle ) {
                    if ( in_array( $needle, $this->values ) ) {
                        return true;
                    }
                }
                break;

            case 'contains':
                foreach ( $this->values as $needle ) {
                    if ( mb_strpos( mb_strtolower( $field_value ), mb_strtolower( $needle ) ) !== false ) {
                        return true;
                    }
                }
                break;
        }
        return false;
    }
}