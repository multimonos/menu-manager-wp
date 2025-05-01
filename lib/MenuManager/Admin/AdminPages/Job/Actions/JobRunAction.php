<?php

namespace MenuManager\Admin\AdminPages\Job\Actions;

use MenuManager\Admin\Types\AdminPostLinkAction;
use MenuManager\Admin\Util\AjaxActionHelper;
use MenuManager\Model\Job;
use MenuManager\Model\Post;
use MenuManager\Tasks\Job\JobRunTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class JobRunAction implements AdminPostLinkAction {

    public function id(): string {
        return 'mm_job_run';
    }

    public function name(): string {
        return __( 'Run', 'menu-manager' );
    }

    public function register(): void {
        AjaxActionHelper::registerHandler( $this );
        AjaxActionHelper::registerFooterScript( $this );

        // Add link to post row bc job is a post type.
        add_filter( 'post_row_actions', function ( $actions, $post ) {
            return Job::isType( $post )
                ? $actions + [$this->id() => $this->link( $post )]
                : $actions;
        }, 9999, 2 );

    }

    public function link( Model|Post|\WP_Post $post ): string {
        return AjaxActionHelper::createLink( $this, $post );
    }

    public function handle(): void {
        // Validate
        AjaxActionHelper::validateOrFail( $this );

        // Get model.
        $job = AjaxActionHelper::findPostOrFail( Job::class );

        // Run.
        $task = new JobRunTask();
        $rs = $task->run( $job->id );

        // Send result.
        AjaxActionHelper::sendResult( $rs, "Job #{$job->id} complete." );
    }

    public function script(): void {
        // Use the default javascript.
        $confirm = true;
        AjaxActionHelper::script( $this, $confirm );
    }
}