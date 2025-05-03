<?php

namespace MenuManager\Admin\AdminPages\Job\Actions;

use MenuManager\Admin\Types\AdminLinkAction;
use MenuManager\Admin\Util\GetActionHelper;
use MenuManager\Model\Job;
use MenuManager\Model\Post;
use MenuManager\Tasks\Generic\DeleteModelTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class DeleteJobAction implements AdminLinkAction {
    public function id(): string {
        return 'mm_job_delete';
    }

    public function name(): string {
        return __( 'Delete', 'menu-manager' );
    }

    public function register(): void {
        GetActionHelper::registerHandler( $this );
    }

    public function link( Post|Model|\WP_Post $model ): string {
        return GetActionHelper::createLink( $this, $model, true );
    }

    public function handle(): void {
        // Validate
        GetActionHelper::validateOrFail( $this );

        // Get model.
        $model = GetActionHelper::findOrRedirect( Job::class );

        // Run
        $task = new DeleteModelTask();
        $rs = $task->run( Job::class, $model->id );

        // Send result.
        GetActionHelper::sendResult( $rs );
    }
}