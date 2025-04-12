<?php

namespace MenuManager\Actions;

use MenuManager\Database\db;
use MenuManager\Database\Model\Job;
use MenuManager\Database\PostType\MenuPost;

class JobRunAction {

    public function canStart( Job $job ): bool {
        return $job->status === Job::STATUS_CREATED;
    }

    public function run( $job_id ): ActionResult {
        db::load();

        // guard : job
        $job = Job::find( $job_id );

        if ( $job === null ) {
            return ActionResult::failure( "Job not found '" . $job_id . "'" );
        }

        // guard : job status
//        if ( ! $this->canStart( $job ) ) {
//            return ActionResult::failure( "Job with status '" . $job->status . "' cannot be started.  Must be '" . Job::STATUS_CREATED . "'." );
//        }

        // split the impex by menu
        $imports = $job->impexes->groupBy( 'menu' );

        $imports->each( function ( $rows, $menu_id ) {

            $menu = MenuPost::find( $menu_id );

            if ( $menu === null ) {
                $create = new MenuCreateAction();
                $create->run( $menu_id, $rows );
            } else {
                $update = new MenuUpdateAction();
                $update->run( $menu, $rows );
            }

        } );

        return ActionResult::success( 'Done' );
    }


}