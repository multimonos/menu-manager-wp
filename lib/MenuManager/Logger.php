<?php

namespace MenuManager;

class Logger {
    const KEY = 'menu-manager';

    public static function info( string $msg ): void {
        error_log( self::KEY . ': ' . $msg );
    }

    public static function taskInfo( string $task, string $msg ): void {
        self::info( "{$task}: {$msg}" );

    }

}