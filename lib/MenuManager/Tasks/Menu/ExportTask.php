<?php

namespace MenuManager\Tasks\Menu;

use MenuManager\Model\Impex;
use MenuManager\Model\Menu;
use MenuManager\Model\Node;
use MenuManager\Service\Database;
use MenuManager\Service\Factory\ExportNodeFactory;
use MenuManager\Tasks\TaskResult;
use MenuManager\Types\Export\ExportConfig;
use MenuManager\Types\Export\Exporter\ExporterFactory;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Collection;

class ExportTask {

    public function run( ExportConfig $config ): TaskResult {

        // Database
        Database::load()::connection()->enableQueryLog();

        // Menu exists.
        $rs = new TaskResult();
        $menus = ExportTaskPeer::getMenus( $config->menuFilter, $rs );
        if ( ! $rs->ok() ) {
            return $rs;
        }

        // Menu tree not empty.
        $rs = new TaskResult();
        $trees = ExportTaskPeer::getMenuTrees( $menus, $rs );
        if ( ! $rs->ok() ) {
            return $rs;
        }
        $trees = null; // free

        // Set the target filename
        if ( empty( $config->target ) ) {
            $config->target = ExportTaskPeer::pathFor( $config, $menus );
        }


        // Row data collection.
        $rows = [Impex::CSV_FIELDS];

        foreach ( $menus as $menu ) {
            $rows = array_merge( $rows, $this->collectRows( $menu ) );
        }


        // Export
        $exporter = ExporterFactory::create( $config );
        $success = $exporter->export( $config, $rows );

        if ( ! $success ) {
            return TaskResult::failure( "Export failed." );
        }

        // Success
        return TaskResult::success( "Export written to {$config->target}" );
    }


    /**
     * Collect data impex rows for a menu.
     *
     * @param Menu $menu
     * @return array
     */
    protected function collectRows( Menu $menu ): array {

        // Get menu root
        $root = Node::findRootNode( $menu );
        if ( $root === null ) {
            return [];
        }

        // Tree
        $tree = Node::getSortedMenu( $menu, $root );

        // Collect Rows
        $rows = $this->visit(
            $tree,
            fn( Node $node, ?Node $page = null ) => ExportNodeFactory::createRow( $menu, $page, $node )
        );

        $tree = null; // free

        $rows = array_filter( $rows );

        return $rows;
    }

    /**
     * Visit each node in the tree and apply a callback function to transform Node -> ImpexRow.
     *
     * @param Collection $nodes
     * @param callable $transformer
     * @return array
     */
    protected function visit( Collection $nodes, callable $transformer, ?Node $page = null ): array {

        $rows = [];

        foreach ( $nodes as $node ) {
            // Update the page.
            if ( $node->isPage() ) {
                $page = $node;
            }

            // Transform the row.
            $row = $transformer( $node, $page );

            // Only add if there is data.
            if ( ! is_null( $row ) ) {
                $rows[] = $row;
            }

            // Visit children.
            if ( $node->children->isNotEmpty() ) {
                $rows = array_merge(
                    $rows,
                    $this->visit( $node->children, $transformer, $page )
                );
            }
        }

        return $rows;
    }


}