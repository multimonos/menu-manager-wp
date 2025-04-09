<?php

namespace MenuManager\Database\Model;

class ImpexMenuFactory {
    public static function createRootNode( \WP_Post $menu ): MenuNode {
        $root = new MenuNode( [
            'menu_id' => $menu->ID,
            'type'    => 'root',
            'title'   => 'menu.' . $menu->post_name,
        ] );

        $root->saveAsRoot();
        $root->refresh();
        $root->fixTree();

        return $root;
    }

    public static function createPageNode( \WP_Post $menu, MenuNode $root, string $page_slug ): MenuNode {
        $page = new MenuNode( [
            'menu_id'   => $menu->ID,
            'parent_id' => $root->id,
            'type'      => 'page',
            'title'     => ucwords( strtolower( $page_slug ) ),
        ] );

        $page->save();
        $page->refresh();
        $root->fixTree();

        return $page;
    }

    public static function createCategoryNode( \WP_Post $menu, Impex $row, MenuNode $parent = null ): MenuNode {
        $n = new MenuNode( [
            'menu_id'     => $menu->ID,
            'title'       => ucwords( mb_strtolower( $row->title ) ),// . '.' . $menu->post_name,
            'type'        => strtolower( $row->type ),
//            'level'       => Impex::levelFromType( $row ),
            'prices'      => $row->prices,
            'description' => $row->description,
        ] );

        if ( $parent instanceof MenuNode ) {
            $n->setParentId( $parent );
        }

        $n->save();
        $n->refresh();

        return $n;
    }

    public static function createMenuItemNode( \WP_Post $menu, Impex $row, MenuNode $parent = null ): MenuNode {

        $node = new MenuNode( [
            'menu_id'     => $menu->ID,
            'title'       => ucwords( mb_strtolower( $row->title ) ),// . '.' . $menu->post_name,
            'type'        => strtolower( $row->type ),
            'prices'      => $row->prices,
            'description' => $row->description,
        ] );

        if ( $parent instanceof MenuNode ) {
            $node->parent_id = $parent->id;
//            $n->setParentId( $parent );
        }

        $node->save();
        $node->refresh();

        $item = new MenuItem( [
            'menu_node_id' => $node->id,
            'prices'       => $row->prices,
            'tags'         => Impex::collectTags( $row ),
            'image_ids'    => $row->image_ids,
        ] );

        $item->save();

        return $node;
    }
}