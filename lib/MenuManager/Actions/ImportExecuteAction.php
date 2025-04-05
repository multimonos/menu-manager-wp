<?php

namespace MenuManager\Actions;

use MenuManager\Database\Model\Job;

class ImportExecuteAction {

    public function canStart( array $job ): bool {
        return $job['status'] === Job::STATUS_VALIDATED;
    }

    public function run( array $job ): ActionResult {

        // guard : job status
        if ( ! $this->canStart( $job ) ) {
            return ActionResult::failure( "Job with status '" . $job['status'] . "' cannot be started.  Must be '" . Job::STATUS_VALIDATED . "'." );
        }

        return ActionResult::success( 'Done' );
    }

}