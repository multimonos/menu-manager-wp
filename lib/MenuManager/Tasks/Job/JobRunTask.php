<?php

namespace MenuManager\Tasks\Job;

use MenuManager\Admin\Util\DateHelper;
use MenuManager\Model\Job;
use MenuManager\Model\Menu;
use MenuManager\Service\Database;
use MenuManager\Service\Logger;
use MenuManager\Tasks\Menu\CreateMenuTask;
use MenuManager\Tasks\Menu\ModifyMenuTask;
use MenuManager\Tasks\TaskResult;
use MenuManager\Utils\UserHelper;

class JobRunTask {

    public function run( $job_id ): TaskResult {
        Database::load();

        // guard : job
        $job = Job::find( $job_id );

        if ( $job === null ) {
            return TaskResult::failure( "Job not found '" . $job_id . "'" );
        }

        Logger::taskInfo( 'run', 'job=' . $job->id );

        // Group by menu.
        $menus = $job->impexes->groupBy( 'menu' );

        $menus->each( function ( $rows, $menu_id ) {

            $menu = Menu::find( $menu_id );

            if ( $menu === null ) {
                // Create menu.
                $create_menu = new CreateMenuTask();
                $create_menu->run( $menu_id, $rows );

            } else {
                // Update menu.
                $modify_task = new ModifyMenuTask();
                $modify_task->run( $menu, $rows );
            }

        } );

        $job->lastrun_at = DateHelper::now();
        $job->lastrun_by = UserHelper::currentUserEmail();
        $job->save();

        return TaskResult::success( "Ran job '{$job->id}'." );
    }


}