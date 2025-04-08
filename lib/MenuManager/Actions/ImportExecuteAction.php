<?php

namespace MenuManager\Actions;

use Illuminate\Support\Collection;
use MenuManager\Database\db;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Job;
use MenuManager\Database\Model\MenuNodeFactory;
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
                $this->create( $menu_id, $rows );
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

    protected function create( $menu_id, Collection $items ): bool {

        db::load()->getConnection()->transaction( function () use ( $menu_id, $items ) {

            // MENU
            $menu = MenuPost::save( ['post_title' => $menu_id, 'post_name' => $menu_id] );

            if ( ! $menu instanceof \WP_Post ) {
                return false;
            }

            // ROOT
            $root = MenuNodeFactory::root( $menu );
            $root->saveAsRoot();
            $root->refresh();
            $root->fixTree();

            // PAGE
            $pages = $items->groupBy( 'page' );

            $pages->each( function ( Collection $rows, string $page_slug ) use ( $menu, $root ) {

                // PAGE
                $page = MenuNodeFactory::pageNode( $menu, $root, $page_slug );
                $page->save();
                $page->refresh();
                $root->fixTree();

                // ALL ITEMS
                $parents = [
                    0 => $page,
                ];
                $level = 0;
                $cnt = 0;

                while ( $cnt < $rows->count() ) {
                    $row = $rows[$cnt];

                    if ( Impex::isCategory( $row ) ) {
                        // CATEGORY
                        $node = MenuNodeFactory::categoryNode( $menu, $row );
                        $node->save();

                        // get parent for this level
                        $parent = $parents[$node->level] ?? null;

                        // guard : parent must exist
                        if ( $parent ) {
                            $node->saveWithParent( $parent );
                            $root->fixTree();

                            // set parent for this level
                            $level = $node->level;
                            $parents[($level + 1)] = $node;
                        }

                        $cnt++;

                    } elseif ( true && Impex::isMenuItemGroup( $row ) ) {
                        // OPTION-GROUP,ADDON-GROUP
                        $parent = $parents[($level + 1)] ?? null;

                        $group = MenuNodeFactory::menuNode( $menu, $row );
                        $group->save();

                        if ( $parent ) {
                            $group->saveWithParent( $parent );
                            $root->fixTree();

                            // OPTIONS
                            $cnt++; // move to first available option

                            while ( $cnt < $rows->count() && in_array( $rows[$cnt]->type, ['option', 'addon'] ) ) {
                                $item = MenuNodeFactory::menuNode( $menu, $rows[$cnt], $group );
                                $item->save();
                                $cnt++;
                            }
                        }

                        $cnt++;

                    } elseif ( true && Impex::isMenuItem( $row ) ) {
                        // ITEM,WINE
                        $parent = $parents[($level + 1)] ?? null;

                        if ( $parent ) {
                            $item = MenuNodeFactory::menuNode( $menu, $row, $parent );
                            $item->save();
                        }
                        $cnt++;

                    } else {
                        // UNKNOWN TYPE
                        error_log( "Unknown impex row type " . json_encode( $row->toArray() ) );
                        $cnt++;
                    }
                }
            } );
        } );

        return true;
    }

    protected function update( \WP_Post $menu, Collection $items ) {
        echo "\nUPDATE: {$menu->post_name}";
        // only take action where specified
        $action_items = $items->filter( fn( $x ) => ! empty( $x->action ) );
        echo "\n- count: " . $action_items->count();


    }
}