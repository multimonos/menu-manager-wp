<?php

namespace MenuManager\Actions;

use MenuManager\Database\Model\Job;

class ImportValidateAction {
    public function canValidate( array $job ): bool {
        return $job['status'] === Job::STATUS_CREATED;
    }

    public function run( array $job ): ActionResult {

        // guard : job status
        if ( ! $this->canValidate( $job ) ) {
            return ActionResult::error( "Invalid job status '" . $job['status'] . "'" );
        }

        return ActionResult::success();
    }
}