<?php

namespace MenuManager\Wpcli\Commands;

use MenuManager\Model\Backup;
use MenuManager\Service\Database;
use MenuManager\Task\BackupTask;
use MenuManager\Task\RestoreTask;
use MenuManager\Wpcli\CliOutput;
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

        $task = new BackupTask();
        $rs = $task->run();

        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }
        WP_CLI::success( $rs->getMessage() );
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

        Database::load();

        if ( Backup::query()->count() === 0 ) {
            return WP_CLI::success( "No backups found." );
        }

        switch ( $format ) {
            case 'count':
                WP_CLI::line( Backup::query()->count() );
                break;

            case 'ids':
                $ids = Backup::all()->pluck( 'id' )->join( ' ' );
                WP_CLI::line( $ids );
                break;

            default:
            case 'table':

                $data = Backup::all()->transform( function ( $x ) {
                    return [
                        'id'         => $x->id,
                        'filename'   => $x->filename,
                        'created_at' => $x->created_at,
                    ];
                } )->toArray();

                $widths = CliOutput::maxLengths( $data );

                CliOutput::table(
                    $widths,
                    ['id', 'filenmae', 'created_at'],
                    $data,
                );
                break;
        }
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

        $task = new RestoreTask();
        $rs = $task->run( $id );

        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }
        WP_CLI::success( $rs->getMessage() );
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
        Database::load();

        $id = intval( $args[0] ?? -1 );

        $backup = Backup::find( $id );

        // failed
        if ( $backup === null ) {
            WP_CLI::error( "Backup not found id=" . $id );
        }

        if ( ! $backup->delete() ) {
            WP_CLI::error( "Failed to delete Backup id=" . $id );
        }

        WP_CLI::success( 'Backup deleted id=' . $id );
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
        Database::load();

        $id = intval( $args[0] ?? -1 );

        $backup = Backup::find( $id );

        // failed
        if ( $backup === null ) {
            WP_CLI::error( "Backup not found id=" . $id );
        }
        WP_CLI::line( $backup->toJson() );
    }


}