<?php

namespace MenuManager\Actions;

use MenuManager\Database\Model\Job;
use stdClass;

class ImportExecuteAction {

    public function canStart( stdClass $job ): bool {
        return $job->status === Job::STATUS_VALIDATED;
    }

    public function run( stdClass $job ): ActionResult {

        // guard : job status
        if ( ! $this->canStart( $job ) ) {
            return ActionResult::failure( "Job with status '" . $job->status . "' cannot be started.  Must be '" . Job::STATUS_VALIDATED . "'." );
        }

        // get menu

        // iter impex items

        // add menu

        // add menu categories

        // add menu items

        return ActionResult::success( 'Done' );

    }

}