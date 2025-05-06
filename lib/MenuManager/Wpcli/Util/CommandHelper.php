<?php

namespace MenuManager\Wpcli\Util;

use MenuManager\Tasks\TaskResult;
use WP_CLI;

class CommandHelper {
    public static function sendTaskResult( TaskResult $rs ): void {
        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }
        WP_CLI::success( $rs->getMessage() );
    }

    public static function sendTaskResultAsJson( TaskResult $rs ): void {
        if ( ! $rs->ok() ) {
            WP_CLI::line( $rs->toJson( true ) );
        } else {
            WP_CLI::line( $rs->getData() );
        }
    }

    public static function sendDataOnly( TaskResult $rs ): void {
        WP_CLI::line( $rs->getData() );
    }
}