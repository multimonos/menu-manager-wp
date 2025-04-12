<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Actions\JobRunAction;
use MenuManager\Database\db;
use MenuManager\Database\Model\Job;
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
    public function list( $args, $assoc_args ) {
        $format = $assoc_args['format'] ?? 'table';

        db::load();

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
                        'created_at' => $x->created_at,
                        'source'     => $x->source,
                    ];
                } )->toArray();

                $maxlen_source = array_reduce(
                    $data,
                    fn( $max, $item ) => max( $max, strlen( $item['source'] ) ),
                    0
                );

                CliOutput::table(
                    [5, 10, 10, 20, $maxlen_source],
                    ['id', 'type', 'status', 'created_at', 'source'],
                    $data,
                );
                break;
        }
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
        db::load();

        $id = $args[0];

        $job = Job::find( $id );

        // failed
        if ( $job === null ) {
            WP_CLI::error( "Job not found id=" . $id );
        }

        // ok
        print_r( $job->toArray() );
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

        // validate
//        $validate = new ImportValidateAction();
//        $rs = $validate->run( $job_id );
//
//        if ( ! $rs->ok() ) {
//            WP_CLI::error( $rs->getMessage() . "\n" . print_r( $rs->getData(), true ) );
//        }

        // run
        $import = new JobRunAction();
        $rs = $import->run( $job_id );

        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }

        WP_CLI::success( $rs->getMessage() );
    }
}
