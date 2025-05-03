<?php

namespace MenuManager\Tasks\Backup;

use MenuManager\Admin\Util\DateHelper;
use MenuManager\Model\Backup;
use MenuManager\Service\Database;
use MenuManager\Service\Filesystem;
use MenuManager\Tasks\TaskResult;

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
        $src = Filesystem::pathFor( $backup->filename );
        if ( ! $fs->exists( $src ) ) {
            return TaskResult::failure( 'Backup restore file missing ' . $backup->filename );
        }

        // Guard sql content
        $sql = $fs->get_contents( $src );

        if ( empty( trim( $sql ) ) ) {
            return TaskResult::failure( 'Backup restore file empty ' . $backup->filename );
        }

        // Restore.
        try {
            $conn = Database::load()->getConnection();
            $conn->unprepared( $sql );

            $backup->lastrun_at = DateHelper::now();
            $backup->save();

        } catch (\Exception $e) {
            return TaskResult::failure( $e->getMessage(), ['exception' => $e] );
        }

        return TaskResult::success( "Restored backup {$backup->id} from {$backup->filename}" );
    }
}