<?php

namespace MenuManager\Tasks\Impex;

use MenuManager\Model\Impex;
use MenuManager\Model\ImpexMeta;
use MenuManager\Model\Job;
use MenuManager\Model\Menu;
use MenuManager\Service\Database;
use MenuManager\Tasks\TaskResult;

class ValidateTask {

    protected $msgs = [];
    protected $warnings = [];
    protected $errors = [];

    protected function warn( string $msg ): void {
        $this->warn[] = $msg;
    }

    protected function err( string $msg ): void {
        $this->errors[] = $msg;
    }

    protected function info( string $msg ): void {
        $this->msgs[] = $msg;
    }

    protected function collect(): array {
        return [
            'info'     => $this->msgs,
            'warnings' => $this->warnings,
            'errors'   => $this->errors,
        ];
    }

    public function run( $job_id ): TaskResult {

        Database::load();

        // ERRORS + WARNINGS

        // create : err : parent_id cannot be set
        // create : err : sort_order cannot be set
        // create : err : item_id cannot be set
        // create : err : title must be set

        // update : err : parent_id cannot be empty
        // update : err : sort_order cannot be empty
        // update : err : item_id cannot be empty
        // update : err : title cannot be empty

        // delete : err : item_id cannot be empty
        // delete : warn : if children then delete will remove children as well


        // throw no errors
        $err = [];
        $msg = [];

        // guard : job
        $job = Job::find( $job_id );

        if ( $job === null ) {
            return TaskResult::failure( "Job not found '" . $job_id . "'" );
        }

        // guard : validated
//        if ( JobStatus::tryFrom( $job->status ) === JobStatus::Validated ) {
//            return TaskResult::success( "Already validated. Nothing to do." );
//        }

        // review the impex
        $rows = $job->impexes();

        // guard : row count
        if ( empty( $rows ) ) {
            $this->err( 'Impex has no rows.' );
            return TaskResult::failure( 'Invalid', $this->collect() );
        }

        // menus
        $menus = $rows->groupBy( 'menu' );
        echo "\n" . get_class( $menus );
        if ( $menus->count() === 0 ) {
            $this->err( 'No menus found.' );
            return TaskResult::failure( 'Invalid', $this->collect() );
        } else {
            $this->info( $menus->count() . " menu(s) will be affected '" . $menus->keys()->join( ',' ) ) . "'";
        }


        $menus->each( function ( $rows ) use ( &$err, &$msg ) {
//            echo "\nrowcount:" . count( $rows );
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
            $menu = Menu::find( $menu_id );

            if ( $menu === null ) { // menu does not exist

                // new menu import must not have actions
                if ( $meta->actions->count() > 0 ) {
                    $ids = $rows->filter( fn( $x ) => ! empty( $x->action ) )->pluck( 'id' );
                    $err[] = "New menu import '{$menu_id}' cannot have any actions set.  Actions '{$meta->actions->join(', ')}' found on row '" . $ids->join( ',' ) . "'.";
                }

            } else { // menu exists
                // existing menu must have at least one action
                if ( $meta->actions->count() === 0 ) {
                    $err[] = "No actions set for '{$menu->post->post_name}' menu import.";
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
//            $job->status = JobStatus::Validated;
//            $job->save();
            return TaskResult::success( 'Validated.', $this->collect() );
        }

        return TaskResult::failure( "Invalid", $this->collect() );

    }


}