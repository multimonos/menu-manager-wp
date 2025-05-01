<?php

namespace MenuManager\Admin\AdminPages\Backup\Actions;

use MenuManager\Admin\Types\AdminPostLinkAction;
use MenuManager\Admin\Util\AjaxActionHelper;
use MenuManager\Model\Backup;
use MenuManager\Model\Post;
use MenuManager\Tasks\Backup\RestoreBackupTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class RestoreBackupAction implements AdminPostLinkAction {
    public function id(): string {
        return 'mm_backup_restore';
    }

    public function name(): string {
        return __( 'Restore', 'menu-manager' );
    }

    public function register(): void {
        AjaxActionHelper::registerHandler( $this );
        AjaxActionHelper::registerFooterScript( $this );
    }

    public function link( Model|Post|\WP_Post $model ): string {
        return AjaxActionHelper::createLink( $this, $model );
    }

    public function handle(): void {
        // Validate
        AjaxActionHelper::validateOrFail( $this );

        // Get model.
        $backup = AjaxActionHelper::findOrFail( Backup::class );

        // Run
        $task = new RestoreBackupTask();
        $rs = $task->run( $backup->id );

        // Send result.
        AjaxActionHelper::sendResult( $rs, "Backup #{$backup->id} restored." );
    }

    public function script(): void {
        // Use the default javascript.
        $confirm = true;
        AjaxActionHelper::script( $this, $confirm );
    }
}