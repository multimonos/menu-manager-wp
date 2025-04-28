<?php

namespace MenuManager\Task;

use MenuManager\Model\Job;
use MenuManager\Model\Menu;
use MenuManager\Service\Database;
use MenuManager\Service\Logger;

class JobRunTask {

//    public function canStart( Job $job ): bool {
//        return $job->status === Job::STATUS_CREATED;
//    }

    public function run( $job_id ): TaskResult {
        Database::load();

        // guard : job
        $job = Job::find( $job_id );

        if ( $job === null ) {
            return TaskResult::failure( "Job not found '" . $job_id . "'" );
        }

        Logger::taskInfo( 'run', 'job=' . $job->post->ID );
        // guard : job status
//        if ( ! $this->canStart( $job ) ) {
//            return ActionResult::failure( "Job with status '" . $job->status . "' cannot be started.  Must be '" . Job::STATUS_CREATED . "'." );
//        }

//        if ( 'import' === $job->type ) {

        // one import task per menu

        $menus = $job->impexes()->groupBy( 'menu' );

        $menus->each( function ( $rows, $menu_id ) {

            $menu = Menu::find( $menu_id );

            if ( $menu === null ) {
                $create_menu = new CreateMenuTask();
                $create_menu->run( $menu_id, $rows );

            } else {
                $modify_task = new ModifyMenuTask();
                $modify_task->run( $menu, $rows );
            }

        } );
//        }

        return TaskResult::success( 'Done' );
    }


}