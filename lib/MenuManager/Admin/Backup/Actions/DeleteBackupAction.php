<?php

namespace MenuManager\Admin\Backup\Actions;

use MenuManager\Admin\Types\AdminPostLinkAction;
use MenuManager\Admin\Util\GetActionHelper;
use MenuManager\Model\Backup;
use MenuManager\Model\Post;
use MenuManager\Task\Backup\DeleteBackupTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class DeleteBackupAction implements AdminPostLinkAction {
    public function id(): string {
        return 'mm_backup_delete';
    }

    public function name(): string {
        return __( 'Delete Permanently', 'menu-manager' );
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
        $backup = GetActionHelper::findOrRedirect( Backup::class );

        // Run
        $task = new DeleteBackupTask();
        $rs = $task->run( $backup->id );

        // Send result.
        GetActionHelper::sendResult( $rs );
    }
}