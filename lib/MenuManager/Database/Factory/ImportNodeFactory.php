<?php

namespace MenuManager\Database\Factory;

use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Node;
use MenuManager\Database\Model\NodeMeta;
use MenuManager\Database\Model\NodeType;

class ImportNodeFactory {

    public static function createRootNode( \WP_Post $menu ): Node {
        $root = new Node( [
            'menu_id'    => $menu->ID,
            'type'       => NodeType::Root->value,
            'title'      => 'menu.' . $menu->post_name,
            'sort_order' => 0,
        ] );

        $root->saveAsRoot();
        $root->refresh();
        $root->fixTree();

        return $root;
    }

    public static function createPageNode( \WP_Post $menu, Node $root, string $page_slug ): Node {
        $page = new Node( [
            'menu_id'   => $menu->ID,
            'parent_id' => $root->id,
            'type'      => NodeType::Page,
            'title'     => $page_slug,
        ] );

        $page->save();
        $page->refresh();
        $root->fixTree();

        return $page;
    }

    public static function createCategoryNode( \WP_Post $menu, Impex $row, Node $parent = null ): Node {
        $node = new Node( [
            'menu_id'     => $menu->ID,
            'uuid'        => $row->uuid,
            'title'       => $row->title,
            'type'        => $row->type,
            'description' => $row->description,
        ] );

        if ( $parent instanceof Node ) {
            $node->parent_id = $parent->id;
//            $node->setParentId( $parent );
        }

        $node->save();
        $node->refresh();

        // Meta data
        if ( ! empty( $row->prices ) ) {
            $item = new NodeMeta( [
                'node_id' => $node->id,
                'prices'  => $row->prices,
            ] );
            $item->save();
        }

        return $node;
    }

    public static function createMenuitemNode( \WP_Post $menu, Impex $row, Node $parent = null ): Node {

        $node = new Node( [
            'menu_id'     => $menu->ID,
            'uuid'        => $row->uuid,
            'type'        => NodeType::from( $row->type ),
            'title'       => $row->title,
            'description' => $row->description,
        ] );

        if ( $parent instanceof Node ) {
            $node->parent_id = $parent->id;
//            $n->setParentId( $parent ); // not reliable
        }

        $node->save();
        $node->refresh();

        // Meta data
        $item = new NodeMeta( [
            'node_id'   => $node->id,
            'prices'    => $row->prices,
            'tags'      => Impex::collectTags( $row ),
            'image_ids' => $row->image_ids,
        ] );
        $item->save();

        return $node;
    }
}