<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Model\Job;
use MenuManager\Tasks\Generic\DeleteModelTask;
use MenuManager\Tasks\Generic\GetLatestModelTask;
use MenuManager\Tasks\Generic\GetModelTask;
use MenuManager\Tasks\Generic\ListModelTask;
use MenuManager\Tasks\Impex\ValidateTask;
use MenuManager\Tasks\Job\JobRunTask;
use MenuManager\Wpcli\Util\CommandHelper;
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
        $format = $assoc_args['format'] ?? 'table';
        $fields = [
            'id',
            'title',
            'status',
            'created_at',
            'filename',
        ];
        $task = new ListModelTask();
        $rs = $task->run( Job::class, $fields, $format );

        CommandHelper::sendDataOnly( $rs );
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

        CommandHelper::sendTaskResultAsJson( $rs );
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
        $id = intval( $args[0] ?? 0 );

        $task = new JobRunTask();
        $rs = $task->run( $id );
        CommandHelper::sendTaskResult( $rs );
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
        $id = intval( $args[0] ?? 0 );

        $task = new GetModelTask();
        $rs = $task->run( Job::class, $id );
        CommandHelper::sendTaskResultAsJson( $rs );
    }

    /**
     * Get most recently created job.
     *
     * ## OPTIONS
     *
     * @when after_wp_load
     */
    public function latest( $args, $assoc_args ) {
        $task = new GetLatestModelTask();
        $rs = $task->run( Job::class );
        CommandHelper::sendTaskResultAsJson( $rs );
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
        $id = intval( $args[0] ?? -1 );
        $task = new DeleteModelTask();
        $rs = $task->run( Job::class, $id );
        CommandHelper::sendTaskResult( $rs );
    }
}
