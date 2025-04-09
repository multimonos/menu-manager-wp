<?php

namespace MenuManager\Actions;

use Illuminate\Support\Collection;
use MenuManager\Database\db;
use MenuManager\Database\Model\Job;
use MenuManager\Database\PostType\MenuPost;

class ImportExecuteAction {

    public function canStart( Job $job ): bool {
        return $job->status === Job::STATUS_VALIDATED;
    }

    public function run( $job_id ): ActionResult {
        db::load();

        // guard : job
        $job = Job::find( $job_id );

        if ( $job === null ) {
            return ActionResult::failure( "Job not found '" . $job_id . "'" );
        }

        // guard : job status
        if ( ! $this->canStart( $job ) ) {
            return ActionResult::failure( "Job with status '" . $job->status . "' cannot be started.  Must be '" . Job::STATUS_VALIDATED . "'." );
        }

        // split the impex by menu
        $imports = $job->impexes->groupBy( 'menu' );

        echo "\nimports:" . $imports->count();

        $imports->each( function ( $rows, $menu_id ) {

            $menu = MenuPost::find( $menu_id );

            if ( $menu === null ) {
                $action = new MenuCreateAction();
                $action->run( $menu_id, $rows );
            } else {
                $this->update( $menu, $rows );
            }

        } );


        // get menu

        // add menu categories

        // add menu items
        echo "\n\n";

        return ActionResult::success( 'Done' );

    }


    protected function update( \WP_Post $menu, Collection $items ) {
        echo "\nUPDATE: {$menu->post_name}";
        // only take action where specified
        $action_items = $items->filter( fn( $x ) => ! empty( $x->action ) );
        echo "\n- count: " . $action_items->count();


    }
}