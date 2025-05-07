<?php

namespace MenuManager\Service\Factory;

use MenuManager\Model\Impex;
use MenuManager\Model\Menu;
use MenuManager\Model\Node;
use MenuManager\Model\NodeMeta;
use MenuManager\Model\Types\NodeType;

class ImportNodeFactory {

    public static function createRootNode( Menu $menu ): Node {
        $root = new Node( [
            'menu_id'    => $menu->post->ID,
            'type'       => NodeType::Root->value,
            'title'      => 'menu.' . $menu->post->post_name,
            'sort_order' => 0,
        ] );

        $root->saveAsRoot();
        $root->refresh();
        $root->fixTree();

        return $root;
    }

    public static function createPageNode( Menu $menu, Node $root, string $page_slug ): Node {
        $page = new Node( [
            'menu_id'   => $menu->post->ID,
            'parent_id' => $root->id,
            'type'      => NodeType::Page,
            'title'     => $page_slug,
        ] );

        $page->save();
        $page->refresh();
        $root->fixTree();

        return $page;
    }

    public static function createCategoryNode( Menu $menu, Impex $row, Node $parent = null ): Node {
        $node = new Node( [
            'menu_id'     => $menu->post->ID,
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

    public static function createMenuitemNode( Menu $menu, Impex $row, Node $parent = null ): Node {

        $node = new Node( [
            'menu_id'     => $menu->post->ID,
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