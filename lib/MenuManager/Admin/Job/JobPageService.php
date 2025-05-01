<?php

namespace MenuManager\Admin\Job;

use MenuManager\Admin\Job\Actions\JobRunAction;
use MenuManager\Admin\Types\AdminPage;
use MenuManager\Admin\Util\EditScreenHelper;
use MenuManager\Model\Job;

class JobPageService implements AdminPage {
    public static function id(): string {
        return 'mm_jobs';
    }

    public static function init(): void {
        EditScreenHelper::disablePostRowActions( Job::type() );

        EditScreenHelper::columnTitles( Job::type(), function ( $columns ) {
            unset( $columns['date'] ); // remove date
            $columns['lastrun_at'] = 'Last Run';
            $columns['created_at'] = 'Created At';
            return $columns;
        } );

        EditScreenHelper::sortableColumns( Job::type(), function ( $columns ) {
            $columns['lastrun_at'] = 'Last Run';
            $columns['created_at'] = 'Created At';
            return $columns;
        } );

        EditScreenHelper::columnData( Job::type(), function ( $column_name, $post_id ) {
            switch ( $column_name ) {
                case 'created_at':
                    echo EditScreenHelper::postCreatedAt( $post_id );
                    break;

                case 'lastrun_at':
                    echo 'Never';
                    break;
            }
        } );

        EditScreenHelper::registerAdminPostActions( [
            new JobRunAction(),
        ] );

        EditScreenHelper::style( Job::type(), '
            .fixed .column-date {width:20%; }
        ' );
    }
}