<?php

namespace MenuManager\Tasks\Generic;

use MenuManager\Service\Database;
use MenuManager\Tasks\TaskResult;

class DeleteModelTask {
    public function run( string $model_class, int|string $id ): TaskResult {
        // Dependencies
        Database::load();

        // Guard : Backup
        $model = $model_class::find( $id );

        if ( $model === null ) {
            return TaskResult::failure( "Record not found {$id}." );
        }

        if ( ! $model->delete() ) {
            return TaskResult::failure( "Failed to delete record {$id}." );
        }

        return TaskResult::success( "Record {$id} deleted." );
    }
}