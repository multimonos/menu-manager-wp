<?php

namespace MenuManager\Task\Backup;

use MenuManager\Model\Backup;
use MenuManager\Service\Database;
use MenuManager\Task\TaskResult;

class DeleteBackupTask {
    public function run( int $id ): TaskResult {
        // Dependencies
        Database::load();

        // Guard : Backup
        $backup = Backup::find( $id );

        if ( $backup === null ) {
            return TaskResult::failure( 'Record not found ' . $id );
        }

        if ( ! $backup->delete() ) {
            return TaskResult::failure( 'Failed to delete record ' . $id );
        }

        return TaskResult::success( "Record {$id} deleted." );
    }
}