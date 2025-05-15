<?php

namespace MenuManager\Tasks\Generic;

use MenuManager\Service\Database;
use MenuManager\Tasks\TaskResult;

class GetLatestModelTask {
    public static function run( string $model_class ): TaskResult {
        Database::load();

        if ( $model_class::query()->count() === 0 ) {
            return TaskResult::failure( "No records found." );
        }

        $model = $model_class::query()->orderby( 'created_at', 'desc' )->first();

        if ( $model === null ) {
            return TaskResult::failure( "No records found." );
        }

        return TaskResult::success( "Found record '{$model->id}'.", $model->toArray() );
    }
}