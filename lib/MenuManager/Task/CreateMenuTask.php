<?php

namespace MenuManager\Task;

use MenuManager\Model\Impex;
use MenuManager\Model\MenuPost;
use MenuManager\Service\Database;
use MenuManager\Service\Factory\ImportNodeFactory;
use MenuManager\Service\Logger;
use MenuManager\Utils\NodeSortOrderManager;
use MenuManager\Vendor\Illuminate\Support\Collection;


class CreateMenuTask {

    public function run( $menu_id, Collection $items ): bool {

        Database::load()->getConnection()->transaction( function () use ( $menu_id, $items ) {

            $sorter = new NodeSortOrderManager();

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

            $pages->each( function ( Collection $rows, string $page_slug ) use ( $sorter, $menu, $root ) {

                // PAGE
                $page = ImportNodeFactory::createPageNode( $menu, $root, $page_slug );
                $sorter->setSort( $page )->save();

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
                            $sorter->setSort( $node )->save();
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
                            $sorter->setSort( $group )->save();
                            $root->fixTree();

                            // OPTIONS,ADDONS
                            $cnt++; // move to first available 'option'

                            while ( $cnt < $rows->count() && Impex::isGroupItemType( $rows[$cnt]->type ) ) {
                                $item = ImportNodeFactory::createMenuitemNode( $menu, $rows[$cnt], $group );
                                $sorter->setSort( $item )->save();
                                $cnt++;
                            }
                        }

                        // $cnt++; // incorrect double increment

                    } elseif ( true && Impex::isItemType( $row->type ) ) {
                        // ITEM,WINE
                        $parent = $parents[($level + 1)] ?? null;

                        if ( $parent ) {
                            $item = ImportNodeFactory::createMenuitemNode( $menu, $row, $parent );
                            $sorter->setSort( $item )->save();
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