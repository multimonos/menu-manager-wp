<?php

namespace MenuManager\Wpcli\Commands;

use MenuManager\Model\Backup;
use MenuManager\Tasks\Backup\CreateBackupTask;
use MenuManager\Tasks\Backup\RestoreBackupTask;
use MenuManager\Tasks\Generic\DeleteModelTask;
use MenuManager\Tasks\Generic\GetLatestModelTask;
use MenuManager\Tasks\Generic\GetModelTask;
use MenuManager\Tasks\Generic\ListModelTask;
use MenuManager\Wpcli\Util\CommandHelper;
use WP_CLI;

class BackupCommands {
    /**
     * Create a sql backup of all menu data.
     *
     *  [--format=<format>]
     *  : Output format. Options: default, json. Default: default
     *
     * @when after_wp_load
     */
    public function create( $args, $assoc_args ) {

        $format = $assoc_args['format'] ?? 'default';

        $task = new CreateBackupTask();
        $rs = $task->run();

        CommandHelper::sendTaskResult( $rs );
    }

    /**
     * List backups.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format. Options: table, ids,json. Default: json.
     *
     * @when after_wp_load
     */
    public function ls( $args, $assoc_args ) {
        $format = $assoc_args['format'] ?? 'table';

        $fields = [
            'id',
            'filename',
            'created_at',
        ];

        $task = new ListModelTask();
        $rs = $task->run( Backup::class, $fields, $format );

        CommandHelper::sendDataOnly( $rs );
    }

    /**
     * Restore sql backup of all menu data.
     *
     * ## OPTIONS
     *
     * <backup_id>
     * : The id of the backup.
     *
     * @when after_wp_load
     */
    public function restore( $args, $assoc_args ) {

        $id = intval( $args[0] ?? -1 );

        $task = new RestoreBackupTask();
        $rs = $task->run( $id );

        CommandHelper::sendTaskResult( $rs );
    }

    /**
     * Get a backup.
     *
     * ## OPTIONS
     *
     * <backup_id>
     * : The id of the backup.
     *
     * @when after_wp_load
     */
    public function get( $args, $assoc_args ) {
        $id = intval( $args[0] ?? 0 );
        $task = new GetModelTask();
        $rs = $task->run( Backup::class, $id );
        CommandHelper::sendTaskResultAsJson( $rs );
    }

    /**
     * Get most recently created backup.
     *
     * ## OPTIONS
     *
     * @when after_wp_load
     */
    public function latest( $args, $assoc_args ) {
        $task = new GetLatestModelTask();
        $rs = $task->run( Backup::class );
        CommandHelper::sendTaskResultAsJson( $rs );
    }

    /**
     * Delete a backup.
     *
     * ## OPTIONS
     *
     * <backup_id>
     * : The id of the backup.
     *
     * @when after_wp_load
     */
    public function rm( $args, $assoc_args ) {
        $id = intval( $args[0] ?? -1 );
        $task = new DeleteModelTask();
        $rs = $task->run( Backup::class, $id );
        CommandHelper::sendTaskResult( $rs );
    }


}