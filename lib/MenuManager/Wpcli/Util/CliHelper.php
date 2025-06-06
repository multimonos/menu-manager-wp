<?php

namespace MenuManager\Wpcli\Util;

use WP_CLI;

class CliHelper {
    public static function table( array $widths, array $headings, array $rows ): string {

        //formats
        $row_fmt = '|' . join( '|', array_map( fn( $n ) => " %-{$n}s ", $widths ) ) . '|';
        $sep_fmt = "+%'" . join( "+%'", array_map( fn( $n ) => "-" . ($n + 2) . 's', $widths ) ) . "+";
        $sep_values = array_fill( 0, count( $widths ), '' );

        // header
        ob_start();
        echo "\n";
        WP_CLI::line( sprintf( $sep_fmt, ...$sep_values ) );
        WP_CLI::line( sprintf( $row_fmt, ...array_values( $headings ) ) );
        WP_CLI::line( sprintf( $sep_fmt, ...$sep_values ) );

        // no rows just end the table
        if ( count( $rows ) === 0 ) {
            WP_CLI::line( sprintf( $sep_fmt, ...$sep_values ) );
            return ob_get_clean();
        }

        // body
        foreach ( $rows as $row ) {
            $values = array_map( fn( $x ) => ($x instanceof \BackedEnum) ? $x->value : $x, array_values( $row ) );
            WP_CLI::line( sprintf( $row_fmt, ...$values ) );
            WP_CLI::line( sprintf( $sep_fmt, ...$sep_values ) );
        }

        return ob_get_clean();
    }

    public static function columnPads( array $fieldnames, array $data ): array {
        $extra = 2;

        // no data, just return fieldname lengths
        if ( empty( $data ) ) {
            return array_map( fn( $x ) => strlen( $x ) + $extra, $fieldnames );
        }

        // data must be assoc
        $keys = array_keys( $data[0] );

        if ( empty( $keys ) ) {
            return array_map( fn( $x ) => strlen( $x ) + $extra, $fieldnames );
        }

        $max = [];

        // min widths
        foreach ( $fieldnames as $field ) {
            $max[$field] = strlen( $field ) + $extra;
        }

        // field widths
        foreach ( $keys as $k ) {
            $values = array_map(
                fn( $x ) => ($x instanceof \BackedEnum) ? $x->value : $x,
                array_column( $data, $k )
            );
            $fieldmax = max( array_map( 'strlen', $values ) ) + $extra;
            $max[$k] = $fieldmax > $max[$k] ? $fieldmax : $max[$k];
        }


        return $max;
    }

}