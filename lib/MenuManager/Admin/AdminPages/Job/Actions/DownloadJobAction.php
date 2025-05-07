<?php

namespace MenuManager\Admin\AdminPages\Job\Actions;

use MenuManager\Admin\Service\NoticeService;
use MenuManager\Admin\Types\AdminLinkAction;
use MenuManager\Admin\Util\GetActionHelper;
use MenuManager\Model\Job;
use MenuManager\Model\Post;
use MenuManager\Service\Filesystem;
use MenuManager\Utils\DownloadHelper;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class DownloadJobAction implements AdminLinkAction {
    public function id(): string {
        return 'mm_job_download';
    }

    public function name(): string {
        return __( 'Download', 'menu-manager' );
    }

    public function register(): void {
        GetActionHelper::registerHandler( $this );
    }

    public function link( Post|Model|\WP_Post $model ): string {
        return GetActionHelper::createLink( $this, $model, false );
    }

    public function handle(): void {
        // Validate
        GetActionHelper::validateOrFail( $this );

        // Get model.
        $model = GetActionHelper::findOrRedirect( Job::class );

        // Run
        $fs = Filesystem::get();

        // File exists.
        $path = Filesystem::pathFor( $model->filename );
        if ( ! $fs->exists( $path ) ) {
            NoticeService::errorRedirect( "Data file not found for Job {$model->id}.", wp_get_referer() );
        }

        // Contents of file.
        $contents = $fs->get_contents( $path );
        if ( empty( $contents ) ) {
            NoticeService::errorRedirect( "Data file empty for Job {$model->id}.", wp_get_referer() );
        }

        // Send
        DownloadHelper::sendHeaders( 'text/csv', $model->title );
        readfile( $path );
        exit;
    }
}