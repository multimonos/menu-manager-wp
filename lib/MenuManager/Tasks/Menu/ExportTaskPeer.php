<?php

namespace MenuManager\Tasks\Menu;

use MenuManager\Model\Menu;
use MenuManager\Model\Node;
use MenuManager\Tasks\TaskResult;
use MenuManager\Types\Export\ExportConfig;

class ExportTaskPeer {
    public static function getMenus( array $ids, TaskResult &$rs ): array {
        $err = [];
        $menus = [];
        foreach ( $ids as $id ) {
            $menu = Menu::find( $id );
            if ( $menu === null ) {
                $err[] = $id;
            } else {
                $menus[] = $menu;
            }
        }

        $rs = empty( $err )
            ? TaskResult::success()
            : TaskResult::failure( "Menu(s) not found: " . join( ', ', $err ) );

        return $menus;
    }

    /**
     * @param Menu[] $menus
     * @param TaskResult $rs
     * @return array
     */
    public static function getMenuTrees( array $menus, TaskResult &$rs ): array {
        $err = [];
        $trees = [];
        foreach ( $menus as $menu ) {
            // Get root node.
            $root = Node::findRootNode( $menu );

            if ( $root === null ) {
                $err[] = $menu;
                continue;
            }

            // Get sorted tree.
            $tree = Node::getSortedMenu( $menu, $root );

            if ( ! $tree || $tree->count() === 0 ) {
                $err[] = $menu;
                continue;
            }

            $trees[] = $tree;
        }

        $rs = empty( $err )
            ? TaskResult::success()
            : TaskResult::failure( "Menu(s) have no nodes: " . join( ', ', array_map( fn( $menu ) => $menu->id . '_' . $menu->post->post_name, $err ) ) );

        return $trees;
    }


    /**
     * @param Menu[] $menus
     * @return string
     */
    public static function pathFor( ExportConfig $config, array $menus ): string {
        // User defined path.
        if ( ! empty( $config->target ) ) {
            return $config->target;
        }

        // Path parts.
        $title = count( $menus ) === 1
            ? $menus[0]->id . '__' . $menus[0]->post->post_name
            : 'mulitple';

        $ext = $config->format->ext();
        $datetime = date( 'Ymd-His' );

        // Path.
        $path = "menu-export__{$title}__{$datetime}{$ext}";

        return $path;
    }


    /**
     * Convert a sparse row of data into a dense one by filling the missing keys with a value.
     *
     * @param array $keys
     * @param array $data
     * @param $fill_value
     * @return array
     */
    public static function fillArray( array $keys, array $data, $fill_value = null ): array {
        $rs = [];
        foreach ( $keys as $k ) {
            $rs[$k] = $data[$k] ?? $fill_value;
        }
        return $rs;
    }
}