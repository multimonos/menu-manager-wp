<?php

namespace MenuManager\Admin\AdminPages\Backup\Actions;

use MenuManager\Admin\Types\AdminPostFormAction;
use MenuManager\Admin\Util\FormActionHelper;
use MenuManager\Admin\Util\GetActionHelper;
use MenuManager\Tasks\Backup\CreateBackupTask;
use MenuManager\Tasks\TaskResult;

class CreateBackupAction implements AdminPostFormAction {

    public function id(): string {
        return 'mm_backup_create';
    }

    public function name(): string {
        return __( 'Create Backup', 'menu-manager' );
    }

    public function register(): void {
        FormActionHelper::registerHandler( $this );
    }

    public function form(): string {
        ob_start();
        ?>
        <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
            <?php echo FormActionHelper::requiredFields( $this, 'large' ); ?>
        </form>
        <?php
        return ob_get_clean();
    }

    public function handle(): void {
        FormActionHelper::validateOrRedirect( $this, wp_get_referer() );

        // Parse the CSV
        $task = new CreateBackupTask();
        $rs = $task->run();

        // Remap the ui message
        $result = $rs->ok()
            ? TaskResult::success( "Created backup {$rs->getData()->id}." )
            : $rs;

        // Send result.
        GetActionHelper::sendResult( $result );
    }
}