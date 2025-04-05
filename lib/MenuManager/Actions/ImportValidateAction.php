<?php

namespace MenuManager\Actions;

use MenuManager\Database\Model\Job;
use stdClass;

class ImportValidateAction {
    public function canValidate( stdClass $job ): bool {
        return $job->status === Job::STATUS_CREATED;
    }

    public function run( stdClass $job ): ActionResult {

        // guard : job status
        if ( ! $this->canValidate( $job ) ) {
            return ActionResult::error( "Invalid job status '" . $job->status . "'" );
        }

        return ActionResult::success();
    }
}