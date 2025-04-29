<?php

namespace MenuManager\Admin\Service;

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
            $columns['created_at'] = 'Created At';
            return $columns;
        } );

        EditScreenHelper::sortableColumns( Job::type(), function ( $columns ) {
            $columns['created_at'] = 'Created At';
            return $columns;
        } );

        EditScreenHelper::columnData( Job::type(), function ( $column_name, $post_id ) {
            if ( $column_name === 'created_at' ) {
                echo EditScreenHelper::postCreatedAt( $post_id );
            }
        } );

        EditScreenHelper::style( Job::type(), '
            .fixed .column-date {width:20%; }
        ' );
    }
}