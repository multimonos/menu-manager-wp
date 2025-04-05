<?php

namespace MenuManager\Wpcli\Commands;

use MenuManager\Actions\ImportExecuteAction;
use MenuManager\Actions\ImportLoadAction;
use MenuManager\Actions\ImportValidateAction;
use MenuManager\Database\Model\Job;
use MenuManager\Import\ImportValidator;
use WP_CLI;


class ImportCommands {
    /**
     * Load a CSV to create an import job.
     *
     * ## OPTIONS
     *
     * <csv_file>
     * : The CSV file to consume.
     *
     * ## EXAMPLES
     *
     *      wp mm import load impex-foobar.csv
     *
     * @when after_wp_load
     */
    public function load( $args, $assoc_args ) {
        list( $path ) = $args;

        // guard : file
        if ( ! file_exists( $path ) ) {
            WP_CLI::error( "File not found $path" );
        }

        // load
        $action = new ImportLoadAction();
        $rs = $action->run( $path );

        // guard : err
        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }

        // ok
        WP_CLI::success( $rs->getMessage() );
    }


    /**
     * Validate an import.
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
        $id = $args[0];

        $job = Job::find( $id );

        // guard : job exists
        if ( $job === null ) {
            WP_CLI::error( "Job not found " . $id );
        }

        WP_CLI::success( "Validating job " . $job['id'] . ' ...' );

        // guard : job status
        $action = new ImportValidateAction();
        $rs = $action->run( $job );

        // guard : err
        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }

        WP_CLI::success( $rs->getMessage() );
    }


    /**
     * Run an import job.
     *
     * ## OPTIONS
     *
     * <job_id>
     * : The impex job to run.
     *
     * ## EXAMPLES
     *
     *      wp mm import apply 42
     *
     * @when after_wp_load
     */
    public function run( $args, $assoc_args ) {
        // job get
        $id = $args[0];

        $job = Job::find( $id );

        // guard : job exists
        if ( $job === null ) {
            WP_CLI::error( "Job not found " . $id );
        }

        WP_CLI::success( "Starting job " . $job['id'] . ' ...' );

        // run job
        $action = new ImportExecuteAction();
        $rs = $action->run( $job );

        // guard : err
        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }

        WP_CLI::success( $rs->getMessage() );
    }
}
