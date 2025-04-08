<?php

namespace MenuManager\Database\Model;

class MenuNodeFactory {
    public static function root( \WP_Post $menu ): MenuNode {
        return new MenuNode( [
            'menu_id' => $menu->ID,
            'type'    => 'root',
            'title'   => 'menu.' . $menu->post_name,
        ] );
    }

    public static function pageNode( \WP_Post $menu, MenuNode $root_node, string $page_slug ): MenuNode {
        return new MenuNode( [
            'menu_id'   => $menu->ID,
            'parent_id' => $root_node->id,
            'type'      => 'page',
            'title'     => ucwords( strtolower( $page_slug ) ),
        ] );
    }

    public static function categoryNode( \WP_Post $menu, Impex $row, MenuNode $parent = null ): MenuNode {
        $n = new MenuNode( [
            'menu_id'     => $menu->ID,
            'title'       => ucwords( strtolower( $row->title ) ),// . '.' . $menu->post_name,
            'type'        => strtolower( $row->type ),
            'level'       => Impex::levelFromType( $row ),
            'prices'      => $row->prices,
            'description' => $row->description,
        ] );

        if ( $parent instanceof MenuNode ) {
            $n->setParentId( $parent );
        }

        return $n;
    }

    public static function menuNode( \WP_Post $menu, Impex $row, MenuNode $parent = null ): MenuNode {

        $n = new MenuNode( [
            'menu_id'     => $menu->ID,
            'title'       => ucwords( strtolower( $row->title ) ),// . '.' . $menu->post_name,
            'type'        => strtolower( $row->type ),
            'prices'      => $row->prices,
            'description' => $row->description,
        ] );

        if ( $parent instanceof MenuNode ) {
            $n->parent_id = $parent->id;
//            $n->setParentId( $parent );
        }

        return $n;
    }


}