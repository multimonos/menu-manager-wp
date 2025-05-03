<?php

namespace MenuManager\Admin\AdminPages\Backup\Actions;

use MenuManager\Admin\Types\AdminLinkAction;
use MenuManager\Admin\Util\GetActionHelper;
use MenuManager\Model\Backup;
use MenuManager\Model\Post;
use MenuManager\Tasks\Generic\DeleteModelTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class DeleteBackupAction implements AdminLinkAction {
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
        $model = GetActionHelper::findOrRedirect( Backup::class );

        // Run
        $task = new DeleteModelTask();
        $rs = $task->run( Backup::class, $model->id );

        // Send result.
        GetActionHelper::sendResult( $rs );
    }
}