<?php

namespace MenuManager\Utils;

class Splitter {
    public static function split( string $str, string $delim = ',' ): array {
        return $str === ''
            ? []
            : preg_split( '/\s*' . preg_quote( $delim, '/' ) . '\s*/', $str, -1, PREG_SPLIT_NO_EMPTY );
    }

    public static function unique( string $str, $delim = ',' ): array {
        return array_unique( self::split( $str, $delim ) );
    }
}