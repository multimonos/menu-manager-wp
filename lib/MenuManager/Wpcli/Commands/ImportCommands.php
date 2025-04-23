<?php

namespace MenuManager\Wpcli\Commands;

use MenuManager\Import\ImportValidator;
use MenuManager\Task\LoadTask;
use MenuManager\Task\ValidateTask;
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
        $task = new LoadTask();
        $rs = $task->run( $path );

        // guard : err
        if ( ! $rs->ok() ) {
            WP_CLI::error( $rs->getMessage() );
        }
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
}
