<?php

namespace MenuManager\Wpcli\Util;

use MenuManager\Task\TaskResult;
use WP_CLI;

class CommandHelper {
    public static function sendTaskResult( TaskResult $rs ): void {
        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }
        WP_CLI::success( $rs->getMessage() );
    }
}