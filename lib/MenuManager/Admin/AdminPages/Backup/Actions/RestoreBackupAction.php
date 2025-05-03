<?php

namespace MenuManager\Admin\AdminPages\Backup\Actions;

use MenuManager\Admin\Types\AdminLinkAction;
use MenuManager\Admin\Util\GetActionHelper;
use MenuManager\Model\Backup;
use MenuManager\Model\Post;
use MenuManager\Tasks\Backup\RestoreBackupTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class RestoreBackupAction implements AdminLinkAction {
    public function id(): string {
        return 'mm_backup_restore';
    }

    public function name(): string {
        return __( 'Restore', 'menu-manager' );
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
        $backup = GetActionHelper::findOrRedirect( Backup::class );

        // Run
        $task = new RestoreBackupTask();
        $rs = $task->run( $backup->id );

        // Send result.
        GetActionHelper::sendResult( $rs, "Backup {$backup->id} restored." );
    }
}
