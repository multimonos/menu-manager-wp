<?php

namespace MenuManager\Task;

use MenuManager\Database\db;
use MenuManager\Database\Factory\ImportNodeFactory;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\PostType\MenuPost;
use MenuManager\Logger;
use MenuManager\Vendor\Illuminate\Support\Collection;

class CreateMenuTask {


    public function run( $menu_id, Collection $items ): bool {

        db::load()->getConnection()->transaction( function () use ( $menu_id, $items ) {

            // MENU
            $menu = MenuPost::create( ['post_title' => $menu_id, 'post_name' => $menu_id] );

            if ( ! $menu instanceof \WP_Post ) {
                return false;
            }
            Logger::taskInfo( 'create', 'menu=' . $menu->post_name );

            // ROOT
            $root = ImportNodeFactory::createRootNode( $menu );

            // PAGE
            $pages = $items->groupBy( 'page' );

            $pages->each( function ( Collection $rows, string $page_slug ) use ( $menu, $root ) {

                // PAGE
                $page = ImportNodeFactory::createPageNode( $menu, $root, $page_slug );

                // ALL ITEMS
                $level = 0;
                $cnt = 0;
                $parents = [
                    0 => $page,
                ];

                while ( $cnt < $rows->count() ) {
                    $row = $rows[$cnt];

                    if ( Impex::isCategoryType( $row->type ) ) {
                        // CATEGORY
                        $node = ImportNodeFactory::createCategoryNode( $menu, $row );
                        $node_level = Impex::levelFromType( $row->type );
                        $root->fixTree();

                        // get parent for this level
                        $parent = $parents[$node_level] ?? null;

                        // guard : parent must exist
                        if ( $parent ) {
                            $node->saveWithParent( $parent );
                            $root->fixTree();

                            // set parent for this level
                            $level = $node_level;
                            $parents[($level + 1)] = $node;
                        }

                        $cnt++;

                    } elseif ( true && Impex::isGroupType( $row->type ) ) {
                        // OPTION-GROUP,ADDON-GROUP
                        $parent = $parents[($level + 1)] ?? null;

                        $group = ImportNodeFactory::createMenuitemNode( $menu, $row );

                        if ( $parent ) {
                            $group->saveWithParent( $parent );
                            $root->fixTree();

                            // OPTIONS,ADDONS
                            $cnt++; // move to first available 'option'

                            while ( $cnt < $rows->count() && Impex::isGroupItemType( $rows[$cnt]->type ) ) {
                                $item = ImportNodeFactory::createMenuitemNode( $menu, $rows[$cnt], $group );
                                $cnt++;
                            }
                        }

                        // $cnt++; // incorrect double increment

                    } elseif ( true && Impex::isItemType( $row->type ) ) {
                        // ITEM,WINE
                        $parent = $parents[($level + 1)] ?? null;

                        if ( $parent ) {
                            $item = ImportNodeFactory::createMenuitemNode( $menu, $row, $parent );
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

}