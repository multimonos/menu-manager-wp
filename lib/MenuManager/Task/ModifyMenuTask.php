<?php

namespace MenuManager\Task;

use MenuManager\Database\db;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\ImpexAction;
use MenuManager\Database\Model\Node;
use MenuManager\Database\Model\NodeType;
use MenuManager\Logger;
use MenuManager\Vendor\Illuminate\Support\Collection;

class ModifyMenuTask {
    public function run( \WP_Post $menu, Collection $rows ) {
//
//        $impex_meta = ImpexMeta::analyze( $rows );
//        print_r( $impex_meta );

        db::load()->getConnection()->transaction( function () use ( $menu, $rows ) {

            Logger::taskInfo( 'modify', 'menu=' . $menu->post_name . ', rows=' . $rows->count() );

            // ROOT
            $root = Node::findRootNode( $menu );

            if ( $root === null ) {
                return TaskResult::failure( 'Root node not found menu_id=' . $menu->ID );
            }


            // PROCESS ROWS
            foreach ( $rows as $row ) {

                // Valid actions only
                $action = ImpexAction::tryFrom( $row->action );

                if ( $action === null ) {
                    continue;
                }

//                print_r( ['row' => $row->toArray()] );

                switch ( $action ) {
                    case ImpexAction::Update:
                        $this->update( $row );
                        break;

                    case ImpexAction::Price:
                        $this->updatePriceOnly( $row );
                        break;

                    case ImpexAction::Delete:
                        $this->delete( $root, $row );
                        break;
                }
            }

//            // PAGE
//            $pages = $items->groupBy( 'page' );
//
//            $pages->each( function ( Collection $rows, string $page_slug ) use ( $menu, $root ) {
//
//                // PAGE
//                $page = ImportNodeFactory::createPageNode( $menu, $root, $page_slug );
//
//                // ALL ITEMS
//                $level = 0;
//                $cnt = 0;
//                $parents = [
//                    0 => $page,
//                ];
//
//                while ( $cnt < $rows->count() ) {
//                    $row = $rows[$cnt];
//
//                    if ( Impex::isCategoryType( $row->type ) ) {
//                        // CATEGORY
//                        $node = ImportNodeFactory::createCategoryNode( $menu, $row );
//                        $node_level = Impex::levelFromType( $row->type );
//                        $root->fixTree();
//
//                        // get parent for this level
//                        $parent = $parents[$node_level] ?? null;
//
//                        // guard : parent must exist
//                        if ( $parent ) {
//                            $node->saveWithParent( $parent );
//                            $root->fixTree();
//
//                            // set parent for this level
//                            $level = $node_level;
//                            $parents[($level + 1)] = $node;
//                        }
//
//                        $cnt++;
//
//                    } elseif ( true && Impex::isGroupType( $row->type ) ) {
//                        // OPTION-GROUP,ADDON-GROUP
//                        $parent = $parents[($level + 1)] ?? null;
//
//                        $group = ImportNodeFactory::createMenuitemNode( $menu, $row );
//
//                        if ( $parent ) {
//                            $group->saveWithParent( $parent );
//                            $root->fixTree();
//
//                            // OPTIONS,ADDONS
//                            $cnt++; // move to first available 'option'
//
//                            while ( $cnt < $rows->count() && Impex::isGroupItemType( $rows[$cnt]->type ) ) {
//                                $item = ImportNodeFactory::createMenuitemNode( $menu, $rows[$cnt], $group );
//                                $cnt++;
//                            }
//                        }
//
//                        // $cnt++; // incorrect double increment
//
//                    } elseif ( true && Impex::isItemType( $row->type ) ) {
//                        // ITEM,WINE
//                        $parent = $parents[($level + 1)] ?? null;
//
//                        if ( $parent ) {
//                            $item = ImportNodeFactory::createMenuitemNode( $menu, $row, $parent );
//                        }
//                        $cnt++;
//
//                    } else {
//                        // UNKNOWN TYPE
//                        error_log( "Unknown impex row type " . json_encode( $row->toArray() ) );
//                        $cnt++;
//                    }
//                }
        } );
    }

    protected function update( Impex $row ) {
        // Node
        $node = Node::find( $row->item_id );

        if ( $node === null ) {
            return;
        }

        $node->fill( [
            'type'        => NodeType::from( $row->type )->value, // must be valid
            'title'       => $row->title,
            'description' => $row->description,
        ] );

        if ( $node->isDirty() ) {
            $node->save();
        }

        // NodeMeta
        $node->meta->fill( [
            'tags'      => Impex::collectTags( $row ),
            'prices'    => $row->prices,
            'image_ids' => $row->image_ids,
        ] );

        if ( $node->meta->isDirty() ) {
            // covers case where id is null and something changed
            $node->meta->save();
        }
    }

    protected function updatePriceOnly( Impex $row ) {
        // Node
        $node = Node::find( $row->item_id );
        if ( $node === null ) {
            return;
        }

        // NodeMeta
        $node->meta->fill( [
            'prices' => $row->prices,
        ] );

        if ( $node->meta->isDirty() ) {
            $node->meta->save();
        }
    }

    protected function delete( Node $rootNode, Impex $row ) {

        $node = Node::find( $row->item_id );

        if ( $node === null ) {
            return;
        }

        $node->deleteOrFail();

        $rootNode->fixTree();

        Logger::info( 'delete node id=' . $node->id );
    }
}