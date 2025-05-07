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

        // body
        foreach ( $rows as $row ) {
            $values = array_map( fn( $x ) => ($x instanceof \BackedEnum) ? $x->value : $x, array_values( $row ) );
            WP_CLI::line( sprintf( $row_fmt, ...$values ) );
            WP_CLI::line( sprintf( $sep_fmt, ...$sep_values ) );
        }

        return ob_get_clean();
    }

    public static function columnPads( array $fieldnames, array $data ): ?array {
        if ( empty( $data ) ) {
            return null;
        }

        $keys = array_keys( $data[0] );

        if ( empty( $keys ) ) {
            return null;
        }

        $max = [];
        $extra = 2;

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