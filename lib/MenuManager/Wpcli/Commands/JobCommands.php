<?php

namespace MenuManager\Wpcli\Commands;


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

        $data = array_map( fn( $x ) => [
            'id'         => $x['id'],
            'type'       => $x['type'],
            'status'     => $x['status'],
            'created_at' => $x['created_at'],
        ], Job::all() );

        CliOutput::table(
            [5, 10, 10, 20],
            ['id', 'type', 'status', 'created_at'],
            $data,
        );
    }
}
