<?php

namespace MenuManager\Task;

use MenuManager\Database\db;
use MenuManager\Database\Model\Job;
use MenuManager\Database\PostType\MenuPost;
use MenuManager\Logger;

class JobRunTask {

//    public function canStart( Job $job ): bool {
//        return $job->status === Job::STATUS_CREATED;
//    }

    public function run( $job_id ): TaskResult {
        db::load();

        // guard : job
        $job = Job::find( $job_id );

        if ( $job === null ) {
            return TaskResult::failure( "Job not found '" . $job_id . "'" );
        }

        Logger::taskInfo( 'run', 'job=' . $job->id );
        // guard : job status
//        if ( ! $this->canStart( $job ) ) {
//            return ActionResult::failure( "Job with status '" . $job->status . "' cannot be started.  Must be '" . Job::STATUS_CREATED . "'." );
//        }

        if ( 'import' === $job->type ) {

            // one import task per menu

            $imports = $job->impexes->groupBy( 'menu' );

            $imports->each( function ( $rows, $menu_id ) {

                $menu = MenuPost::find( $menu_id );

                if ( $menu === null ) {
                    $create = new CreateTask();
                    $create->run( $menu_id, $rows );
                } else {
                    $update = new UpdateTask();
                    $update->run( $menu, $rows );
                }

            } );
        }

        return TaskResult::success( 'Done' );
    }


}