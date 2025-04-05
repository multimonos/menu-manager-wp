<?php

namespace MenuManager\Wpcli;

use WP_CLI;

class CliOutput {
    public static function table( array $widths, array $headings, array $rows ) {

        //formats
        $row_fmt = '|' . join( '|', array_map( fn( $n ) => " %-{$n}s ", $widths ) ) . '|';
        $sep_fmt = "+%'" . join( "+%'", array_map( fn( $n ) => "-" . ($n + 2) . 's', $widths ) ) . "+";
        $sep_values = array_fill( 0, count( $widths ), '' );

        // header
        echo "\n";
        WP_CLI::line( sprintf( $sep_fmt, ...$sep_values ) );
        WP_CLI::line( sprintf( $row_fmt, ...array_values( $headings ) ) );
        WP_CLI::line( sprintf( $sep_fmt, ...$sep_values ) );

        // body
        foreach ( $rows as $row ) {
            $values = array_values( $row );
            WP_CLI::line( sprintf( $row_fmt, ...array_values( $row ) ) );
            WP_CLI::line( sprintf( $sep_fmt, ...$sep_values ) );
        }
    }
}