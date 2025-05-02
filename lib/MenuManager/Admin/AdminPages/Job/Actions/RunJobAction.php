<?php

namespace MenuManager\Admin\AdminPages\Job\Actions;

use MenuManager\Admin\Types\AdminPostLinkAction;
use MenuManager\Admin\Util\GetActionHelper;
use MenuManager\Model\Job;
use MenuManager\Model\Post;
use MenuManager\Tasks\Job\JobRunTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class RunJobAction implements AdminPostLinkAction {

    public function id(): string {
        return 'mm_job_run';
    }

    public function name(): string {
        return __( 'Run', 'menu-manager' );
    }

    public function register(): void {
        GetActionHelper::registerHandler( $this );
    }


    public function link( Model|Post|\WP_Post $model ): string {
        return GetActionHelper::createLink( $this, $model, true );
    }

    public function handle(): void {
        // Validate
        GetActionHelper::validateOrFail( $this );

        // Get model.
        $model = GetActionHelper::findOrRedirect( Job::class );

        // Run.
        $task = new JobRunTask();
        $rs = $task->run( $model->id );

        // Send result.
        GetActionHelper::sendResult( $rs );
    }
}