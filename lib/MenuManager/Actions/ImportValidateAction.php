<?php

namespace MenuManager\Actions;

use MenuManager\Database\Model\Job;

class ImportValidateAction {
    public function canValidate( Job $job ): bool {
        return $job->status === Job::STATUS_CREATED;
    }

    public function run( Job $job ): ActionResult {

        // guard : job status
        if ( ! $this->canValidate( $job ) ) {
            return ActionResult::error( "Invalid job status '" . $job->status . "'" );
        }

        return ActionResult::success();
    }
}