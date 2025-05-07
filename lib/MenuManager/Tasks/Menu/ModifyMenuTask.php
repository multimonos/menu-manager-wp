<?php

namespace MenuManager\Tasks\Menu;

use MenuManager\Model\Impex;
use MenuManager\Model\Menu;
use MenuManager\Model\Node;
use MenuManager\Model\Types\ImpexAction;
use MenuManager\Model\Types\NodeType;
use MenuManager\Service\Database;
use MenuManager\Service\Logger;
use MenuManager\Tasks\TaskResult;
use MenuManager\Vendor\Illuminate\Support\Collection;

class ModifyMenuTask {
    public function run( Menu $menu, Collection $rows ) {
//
//        $impex_meta = ImpexMeta::analyze( $rows );
//        print_r( $impex_meta );

        Database::load()->getConnection()->transaction( function () use ( $menu, $rows ) {

            Logger::taskInfo( 'modify', 'menu=' . $menu->post->post_name . ', rows=' . $rows->count() );

            // ROOT
            $root = Node::findRootNode( $menu );

            if ( $root === null ) {
                return TaskResult::failure( 'Root node not found menu_id=' . $menu->post->ID );
            }


            // PROCESS ROWS
            foreach ( $rows as $row ) {

                // Valid actions only
                $action = ImpexAction::tryFrom( $row->action );

                if ( $action === null ) {
                    continue;
                }

                // Process action.
                switch ( $action ) {

                    case ImpexAction::Insert:
                        // @todo implement impex action "insert"
                        Logger::taskInfo( 'modify-menu', 'unhandled row action in impex : ' . ImpexAction::Insert->value );
                        break;

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

            $root->fixTree();
        } );
    }

    protected function update( Impex $row ) {
        // Node
        $node = Node::find( $row->item_id );

        if ( $node === null ) {
            return;
        }

        $data = [
            'type'        => NodeType::from( $row->type )->value, // must be valid
            'title'       => $row->title,
            'description' => $row->description,
            'sort_order'  => $row->sort_order,
            'parent_id'   => $row->parent_id,
        ];


        $node->fill( $data );

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