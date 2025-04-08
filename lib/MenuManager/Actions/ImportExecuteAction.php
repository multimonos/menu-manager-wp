<?php

namespace MenuManager\Actions;

use Illuminate\Support\Collection;
use MenuManager\Database\db;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Job;
use MenuManager\Database\Model\MenuNode;
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
//        db::load();
        db::load()->getConnection()->transaction( function () use ( $menu_id, $items ) {

            // MENU
            $menu = MenuPost::save( ['post_title' => $menu_id, 'post_name' => $menu_id] );

            if ( ! $menu instanceof \WP_Post ) {
                return false;
            }

            // ROOT
            $root = MenuNode::create( [
                'menu_id' => $menu->ID,
                'title'   => 'menu.' . $menu->post_name,
                'type'    => 'root',

            ] );
            $root->saveAsRoot();
            $root->refresh();
            $root->fixTree();

            // PAGE
            $pages = $items->groupBy( 'page' );

            $pages->each( function ( $page_items, $page_slug ) use ( $menu, $root ) {

                // page
                $page = new MenuNode( [
                    'menu_id'   => $menu->ID,
                    'parent_id' => $root->id,
                    'type'      => 'page',
                    'title'     => ucwords( strtolower( $page_slug ) ),
                ] );

                $page->save();
                $page->refresh();
                $root->fixTree();

                // keep a local parent store
                $parents = [
                    0 => $page,
                ];

                // ITEMS
                $page_items->each( function ( $item ) use ( $menu, $root, $page, &$parents ) {

                    // CATEGORY
                    if ( $item->isCategory() ) {
                        $node = Impex::menuNodeOf( $menu, $item );

                        // get parent for this level
                        $parent = $parents[$node->level] ?? null;

                        // guard : parent must exist
                        if ( $parent ) {
                            $node->parent_id = $parent->id;
                            $node->save();
                            $node->refresh();

                            // always fix the tree
                            $root->fixTree();

                            // set parent for this level
                            $parents[($node->level + 1)] = $node;
                        }
                    }


                } );


//                if ( false ) {

//                    print_r( $page->toArray() );
//
//                    $currentLevel = 0;
//                    $currentCategory = null;
//                    $parents = [];
//                    foreach ( $page_items as $row ) {
//
//                        if ( $row->isCategory() ) {
//                            $category = Impex::menuCategoryOf( $row );
//                            $category->menuPage()->associate( $page );
//                            $category->save();
//                            $category->refresh();
//
//                            if ( $category->level === 0 ) {
//                                // top level category
//                                $category->saveAsRoot();
//
//                            } elseif ( strtolower( $category->title ) === 'frozen' ) {
//                                $parent = $parents[$category->level - 1] ?? null;
//                                $child = $category;
//
//                                echo "\n  parent {$parent->title} -> child {$child->title}";
//                                if ( $parent && $parent->exists ) {
//                                    echo "\n  parent exists : " . ($parent && $parent->exists ? 'yes' : 'no');
//                                    $parent->refresh();
//                                    $child->appendToNode( $parent )->save();
//                                }
//                            }
//
//                            $parents[$category->level] = $category;
//
////                        print_r( $parents );
////                        if ( $currentCategory && $category->level > 0 ) {
////                            echo "\n:parent:" . $currentCategory->id;
////                            $category->parent()->associate( $currentCategory )->save();
////                        }
//
//                            // debug
//                            echo "\n> {$category->level}  {$category->title} {$category->type}";
//
//                            // next
//                            $currentCategory = $category;
//                            $currentLevel = $category->level;
//
//                        } elseif ( $row->isGroup() ) {
//                            continue;
//                            $category = Impex::menuCategoryOf( $row );
//                            $category->level = $currentLevel + 1;
//                            $category->menuPage()->associate( $page );
//                            $category->save();
//
//                            // debug
//                            echo "\nG {$category->level}  {$category->title} {$category->type}";
//
//                            // next
//                            $currentCategory = $category;
//
//                        } elseif ( $row->isMenuItem() ) {
//                            continue;
//                            $menuitem = Impex::menuItemOf( $row );
//                            if ( $currentCategory ) {
//                                $menuitem->menuCategory()->associate( $currentCategory );
//                                $menuitem->save();
//                            }
//                            echo "\n  - {$menuitem->title}  {$menuitem->type}";
//                        }
//                    }
//                }
            } );
            MenuNode::fixTree();
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