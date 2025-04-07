<?php

namespace MenuManager\Actions;

use Illuminate\Support\Collection;
use MenuManager\Database\db;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Job;
use MenuManager\Database\Model\MenuPage;
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

        $menu = MenuPost::save( ['post_title' => $menu_id, 'post_name' => $menu_id] );

        print_r( $menu );
        if ( ! $menu instanceof \WP_Post ) {
            echo "\nNOPE";
            print_r( $menu );
            return false;
        }
        db::load()->getConnection()->transaction( function () use ( $menu, $items ) {

            echo "\nCREATE: {$menu->ID}";
            echo "\n- items: " . $items->count();
            $pages = $items->groupBy( 'page' );
            echo "\n- pages: " . $pages->count();


            $pages->each( function ( $page_items, $page_slug ) use ( $menu ) {
                echo "\n- {$page_slug}";

                $page = new MenuPage( [
                    'menu_post_id' => $menu->ID,
                    'page'         => $page_slug,
                ] );

                $page->save();

                print_r( $page->toArray() );

                $currentLevel = 0;
                $currentCategory = null;
                foreach ( $page_items as $row ) {

                    if ( $row->isCategory() ) {
                        $category = Impex::menuCategoryOf( $row );
                        $category->menuPage()->associate( $page );
                        $category->save();
                        $currentLevel = $category->level;
                        $currentCategory = $category;

                        echo "\n> {$category->level}  {$category->title} {$category->type}";

                    } elseif ( $row->isGroup() ) {
                        $category = Impex::menuCategoryOf( $row );
                        $category->level = $currentLevel + 1;
                        $category->menuPage()->associate( $page );
                        $category->save();
                        $currentCategory = $category;

                        echo "\nG {$category->level}  {$category->title} {$category->type}";

                    } elseif ( $row->isMenuItem() ) {
                        $menuitem = Impex::menuItemOf( $row );
                        if ( $currentCategory ) {
                            $menuitem->menuCategory()->associate( $currentCategory );
                            $menuitem->save();
                        }
                        echo "\n  - {$menuitem->title}  {$menuitem->type}";
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