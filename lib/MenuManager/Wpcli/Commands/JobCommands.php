<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Model\Job;
use MenuManager\Task\JobRunTask;
use MenuManager\Task\ValidateTask;
use WP_CLI;

class JobCommands {

    /**
     * List jobs.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format. Options: table, ids,json. Default: json.
     *
     * ## EXAMPLES
     *
     *      wp mm jobs list
     *
     * @when after_wp_load
     */
    public function ls( $args, $assoc_args ) {
        $format = $assoc_args['format'] ?? 'json';
        $cmd = sprintf( "post list --post_type=%s --format=%s", Job::type(), $format );
        WP_CLI::runcommand( $cmd );
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
        $id = $args[0];

        $task = new ValidateTask();
        $rs = $task->run( $id );

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
     * <job_id>
     * : The id of the job ot get.
     *
     * @when after_wp_load
     */
    public function get( $args, $assoc_args ) {
        $id = $args[0];

        if ( is_numeric( $id ) ) {
            WP_CLI::runcommand( "post get {$id} --format=json" );
        } else {
            $job = Job::find( $id );

            if ( $job === null ) {
                WP_CLI::error( "Job not found '$id'." );
            }
            WP_CLI::runcommand( "post get {$job->post->ID} --format=json" );
        }
    }

    /**
     * Get most recently created job.
     *
     * ## OPTIONS
     *
     * @when after_wp_load
     */
    public function latest( $args, $assoc_args ) {
        WP_CLI::runcommand( sprintf( "post list --post_type=%s --orderby=date --order=desc --posts_per_page=1 --format=ids", Job::type() ) );
    }

    /**
     * Run an import job.
     *
     * ## OPTIONS
     *
     * <job_id>
     * : The job to run.
     *
     * ## EXAMPLES
     *
     *      wp mm job run 42
     *
     * @when after_wp_load
     */
    public function run( $args, $assoc_args ) {
        $id = $args[0];

        $task = new JobRunTask();
        $rs = $task->run( $id );

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
        $id = $args[0];

        if ( ! is_numeric( $id ) ) {
            WP_CLI::error( "Delete requires a numeric id." );
        }

        WP_CLI::runcommand( "post delete {$id} --force" );
    }
}
