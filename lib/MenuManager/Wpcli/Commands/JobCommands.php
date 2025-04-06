<?php

namespace MenuManager\Wpcli\Commands;


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
     * ## EXAMPLES
     *
     *      wp mm jobs list
     *
     * @when after_wp_load
     */
    public function list( $args, $assoc_args ) {
        db::load();

        $data = Job::all()->transform( function ( $x ) {
            return ['id'         => $x->id,
                    'type'       => $x->type,
                    'status'     => $x->status,
                    'created_at' => $x->created_at,
            ];
        } )->toArray();

        CliOutput::table(
            [5, 10, 10, 20],
            ['id', 'type', 'status', 'created_at'],
            $data,
        );
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
}
