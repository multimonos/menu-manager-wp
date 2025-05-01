<?php

namespace MenuManager\Task\Backup;

use MenuManager\Model\Backup;
use MenuManager\Service\Database;
use MenuManager\Service\Filesystem;
use MenuManager\Task\TaskResult;

class RestoreBackupTask {
    public function run( int $id ): TaskResult {
        // Dependencies
        $fs = Filesystem::get();
        Database::load();

        // Guard : Backup
        $backup = Backup::find( $id );

        if ( $backup === null ) {
            return TaskResult::failure( 'Backup not found id=' . $id );
        }

        // Guard sql file
        if ( ! $fs->exists( $backup->filepath() ) ) {
            return TaskResult::failure( 'Backup restore file missing ' . $backup->filename );
        }

        // Guard sql content
        $sql = $fs->get_contents( $backup->filepath() );

        if ( empty( trim( $sql ) ) ) {
            return TaskResult::failure( 'Backup restore file empty ' . $backup->filename );
        }

        // Restore.
        try {
            $conn = Database::load()->getConnection();
            $conn->unprepared( $sql );
        } catch (\Exception $e) {
            return TaskResult::failure( $e->getMessage(), ['exception' => $e] );
        }

        return TaskResult::success( "Restored backup {$backup->id} from {$backup->filepath()}" );
    }
}