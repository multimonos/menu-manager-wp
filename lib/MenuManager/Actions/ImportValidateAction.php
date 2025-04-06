<?php

namespace MenuManager\Actions;

use MenuManager\Database\db;
use MenuManager\Database\Model\Job;

class ImportValidateAction {
    public function canStart( Job $job ): bool {
        return $job->status === Job::STATUS_CREATED;
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

        // current : status
        echo "\ncurrent-status: " . $job->status;

        // update
        $job->status = Job::STATUS_VALIDATED;

        // save
        $job->save();


        echo "\nupdated-status: " . $job->status;

        return ActionResult::success( 'Validated' );
    }
}