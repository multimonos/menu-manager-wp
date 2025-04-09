<?php

namespace MenuManager\Actions;

use MenuManager\Database\db;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\ImpexMeta;
use MenuManager\Database\Model\Job;
use MenuManager\Database\PostType\MenuPost;

class ImportValidateAction {
    public function canStart( Job $job ): bool {
        return $job->status === Job::STATUS_CREATED;
    }

    public function run( $job_id ): ActionResult {

        db::load();

        // throw fatal errors only
        $err = [];
        $msg = [];

        // guard : job
        $job = Job::find( $job_id );

        if ( $job === null ) {
            return ActionResult::failure( "Job not found '" . $job_id . "'" );
        }

        // guard : validated
        if ( $job->status === Job::STATUS_VALIDATED ) {
            return ActionResult::success( "Already validated. Nothing to do." );
        }

        // guard : job status
        if ( ! $this->canStart( $job ) ) {
            return ActionResult::failure( "Job with status '" . $job->status . "' cannot be started.  Must be '" . Job::STATUS_CREATED . "'." );
        }

        // review the impex
        $rows = $job->impexes;

        // guard : row count
        if ( empty( $rows ) ) {
            return ActionResult::failure( 'Impex has no rows' );
        }

        echo "\n--- " . date( 'YmdHis' ) . " ---\n";

        // meta
        $menus = $rows->groupBy( 'menu' );
        if ( $menus->count() === 0 ) {
            return ActionResult::failure( 'No menus found' );
        }
        echo "\nmenu-count:" . $menus->count();


        $menus->each( function ( $rows ) use ( &$err, &$msg ) {
            echo "\nrowcount:" . count( $rows );
//            print_r( $rows );

//            print_r( $rows->pluck( 'action' )->unique() );
            $meta = ImpexMeta::analyze( $rows );

            $msg[] = [
                'job: ' . $meta->jobId->join( ',' ),
                'rows: ' . $meta->rowCount,
                'menu-count: ' . $meta->menuIds->count(),
                'menu-ids: ' . $meta->menuIds->join( ',' ),
                'actions: ' . $meta->actions->join( ',' ),
                'types: ' . $meta->types->join( ',' ),
            ];

            $menu_id = $meta->menuIds->first();
            $menu = MenuPost::find( $menu_id );

            if ( $menu === null ) { // menu does not exist

                // new menu import must not have actions
                if ( $meta->actions->count() > 0 ) {
                    $ids = $rows->filter( fn( $x ) => ! empty( $x->action ) )->pluck( 'id' );
                    $err[] = "New menu import '{$menu_id}' cannot have any actions set.  Actions '{$meta->actions->join(', ')}' found on row '" . $ids->join( ',' ) . "'.";
                }

            } else { // menu exists
                // existing menu must have at least one action
                if ( $meta->actions->count() === 0 ) {
                    $err[] = "No actions set for '{$menu->post_name}' menu import.";
                }
            }


//            $rows->map( function ( $x ) {
//                echo "\nid:" . $x->id . "--" . print_r( $x->toArray(), true );
//            } );
        } );


        print_r( [
            'err' => $err,
            'msg' => $msg,
        ] );

        $r = Impex::find( 450 );
//        print_r( $r->toArray() );
//        print_r( $meta );

        // basic feedback
//        $msg[] = 'rows: ' . $meta->rowCount;
//        $msg[] = 'menu-count: ' . $meta->menuIds->count();
//        $msg[] = 'menu-ids: ' . $meta->menuIds->join( ',' );
//        $msg[] = 'actions: ' . $meta->actions->join( ',' );
//        $msg[] = 'types: ' . $meta->types->join( ',' );

//        if ( $meta->menuIds->count() === 0 ) {
//            $err[] = "No menus found";
//        }

        // iterate each impex

        // valid
        if ( empty( $err ) ) {
            $job->status = Job::STATUS_VALIDATED;
            $job->save();
            return ActionResult::success( 'Validated', $msg );
        }

        return ActionResult::failure( "Failed to validate", $err );

    }


}