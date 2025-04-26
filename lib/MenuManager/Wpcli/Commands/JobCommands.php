<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Model\Job;
use MenuManager\Service\Database;
use MenuManager\Task\JobRunTask;
use MenuManager\Task\ValidateTask;
use MenuManager\Wpcli\CliOutput;
use WP_CLI;

class JobCommands {

    /**
     * List jobs.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format. Options: table, ids. Default: table.
     *
     * ## EXAMPLES
     *
     *      wp mm jobs list
     *
     * @when after_wp_load
     */
    public function ls( $args, $assoc_args ) {
        $format = $assoc_args['format'] ?? 'table';

        Database::load();

        switch ( $format ) {
            case 'count':
                WP_CLI::line( Job::all()->pluck( 'id' )->count() );
                break;

            case 'ids':
                $ids = Job::all()->pluck( 'id' )->join( ' ' );
                WP_CLI::line( $ids );
                break;

            default:
            case 'table':

                $data = Job::all()->transform( function ( $x ) {
                    return [
                        'id'         => $x->id,
                        'type'       => $x->type,
                        'status'     => $x->status,
                        'source'     => $x->source,
                        'created_at' => $x->created_at,
                    ];
                } )->toArray();

                $maxlen_source = array_reduce(
                    $data,
                    fn( $max, $item ) => max( $max, strlen( $item['source'] ) ),
                    0
                );

                CliOutput::table(
                    [5, 10, 10, $maxlen_source, 20],
                    ['id', 'type', 'status', 'source', 'created_at'],
                    $data,
                );
                break;
        }
    }

    /**
     * Validate a job.
     *
     * ## OPTIONS
     *
     * <job_id>
     * : The impex job to validate.
     *
     * ## EXAMPLES
     *
     *      wp mm import validate 42
     *
     * @when after_wp_load
     */
    public function validate( $args, $assoc_args ) {

        // job get
        $job_id = $args[0];

        // guard : job status
        $task = new ValidateTask();
        $rs = $task->run( $job_id );

        // guard : err
        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() . "\n" . print_r( $rs->getData(), true ) );
        }
        WP_CLI::success( $rs->getMessage() . "\n" . print_r( $rs->getData(), true ) );
    }

    /**
     * Get details about a job.
     *
     * ## OPTIONS
     *
     * <id>
     * : The id of the job ot get.
     *
     * @when after_wp_load
     */
    public function get( $args, $assoc_args ) {
        Database::load();

        $id = $args[0];

        $job = Job::find( $id );

        // failed
        if ( $job === null ) {
            WP_CLI::error( "Job not found id=" . $id );
        }

        echo $job->toJson();
    }


    /**
     * Run an import job.
     *
     * ## OPTIONS
     *
     * <id>
     * : The job to run.
     *
     * ## EXAMPLES
     *
     *      wp mm job run 42
     *
     * @when after_wp_load
     */
    public function run( $args, $assoc_args ) {
        $job_id = $args[0];

        $task = new JobRunTask();
        $rs = $task->run( $job_id );

        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }
        WP_CLI::success( $rs->getMessage() );
    }


    /**
     * Delete a job.
     *
     * ## OPTIONS
     *
     * <job_id>
     * : The id of the job.
     *
     * @when after_wp_load
     */
    public function rm( $args, $assoc_args ) {
        Database::load();

        $id = $args[0];

        $obj = Job::find( $id );

        // failed
        if ( $obj === null ) {
            WP_CLI::error( "Node not found id=" . $id );
        }

        if ( ! $obj->delete() ) {
            WP_CLI::error( "Failed to delete Job id=" . $id );
        }

        WP_CLI::success( 'Job deleted id=' . $id );
    }
}
