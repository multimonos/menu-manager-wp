<?php

namespace MenuManager\Utils;

class EnumTools {
    public static function values( string $enumClass ): array {
        return array_map( fn( $case ) => $case->value, $enumClass::cases() );
    }

    public static function csv( string $enum_class ): string {
        return join( ', ', self::values( $enum_class ) );
    }
}