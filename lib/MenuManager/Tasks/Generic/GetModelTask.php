<?php

namespace MenuManager\Tasks\Generic;

use MenuManager\Service\Database;
use MenuManager\Tasks\TaskResult;

class GetModelTask {
    public static function run( string $model_class, int|string $id ): TaskResult {
        Database::load();

        $id = intval( $id );

        $model = $model_class::find( $id );

        if ( $model === null ) {
            return TaskResult::failure( "Record '{$id}' not found." );
        }

        return TaskResult::success( "Found record '{$id}.", $model->toArray() );
    }
}