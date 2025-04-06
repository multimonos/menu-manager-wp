<?php

namespace MenuManager\Actions;

use MenuManager\Database\db;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Job;

class ImportExecuteAction {

    public function canStart( Job $job ): bool {
        return $job->status === Job::STATUS_VALIDATED;
    }

    public function run( $job_id ): ActionResult {
        db::load();

        // guard : job
        $jobs = Job::find( $job_id );

        if ( $jobs === null ) {
            return ActionResult::failure( "Job not found '" . $job_id . "'" );
        }

        // pluck
        $job = $jobs->first();

        // guard : job status
        if ( ! $this->canStart( $job ) ) {
            return ActionResult::failure( "Job with status '" . $job->status . "' cannot be started.  Must be '" . Job::STATUS_VALIDATED . "'." );
        }

        // get menu
//        return ActionResult::success( 'running...' );

        // iter impex itemso
        $rows = Impex::where( 'job_id', $job->id );

        if ( $rows === null || $rows->count() === 0 ) {
            return ActionResult::success( "Import has no rows" );
        }

        // impex meta
        $menus = $rows->pluck( 'menu' )->unique();
        $pages = $rows->pluck( 'page' )->unique();
        $actions = $rows->pluck( 'action' )->unique();

        // iter rows
        $rows->get()->each( function ( $row ) {
            echo "\n" . $row->title;
        } );

        print_r( [
            'rows'    => $rows->count(),
            'menus'   => $menus->join( "," ),
            'pages'   => $pages->join( "," ),
            'actions' => $actions->join( "," ),
        ] );

        // add menu

        // add menu categories

        // add menu items

        return ActionResult::success( 'Done' );

    }

}