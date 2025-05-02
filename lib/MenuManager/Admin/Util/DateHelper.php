<?php

namespace MenuManager\Admin\Util;

use MenuManager\Vendor\Carbon\Carbon;

class DateHelper {
    public static function format( $date ): string {
        return Carbon::parse( $date )->format( 'Y/m/d \a\t g:i a' );
    }

    public static function delta( $date, $null_value = 'Never' ): string {
        return $date
            ? Carbon::parse( $date )->diffForHumans()
            : $null_value;
    }

    public static function now() {
        return Carbon::now();
    }
}