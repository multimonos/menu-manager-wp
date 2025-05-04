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
//        print_r( $config );

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

    protected function collectRows( Menu $menu ): array {

        // @todo Review this decision to make the hierarchy implicit in
        // the output as user cannot update the "pages" if they are
        // not rows in the export.

        // I have done this so that "page" can be a column ...
        $page_names = Node::findPageNames( $menu );

        if ( empty( $page_names ) ) {
            return [];
        }


        // Collect.
        $rows = [];

        foreach ( $page_names as $page_name ) {
            $parent = Node::findPageNode( $menu, $page_name );
            $tree = Node::getSortedMenu( $menu, $parent );

            // Sparse data collection.
            $sparse_rows = $this->visit(
                $tree,
                fn( Node $node ) => ExportNodeFactory::createRow( $menu, $page_name, $node )
            );

            // Evolve sparse data.
            foreach ( $sparse_rows as $sparse_row ) {
                $dense_row = ExportTaskPeer::fillArray( Impex::CSV_FIELDS, $sparse_row );
                $rows[] = $dense_row;
            }
        }

        return $rows;
    }

    /**
     * Visit each node in the tree and apply a callback function to transform data.
     *
     * @param Collection $nodes
     * @param callable $transformer
     * @return array
     */
    protected function visit( Collection $nodes, callable $transformer ): array {

        $rows = [];

        foreach ( $nodes as $node ) {
            $nrow = $transformer( $node );

            if ( ! is_null( $nrow ) ) {
                $rows[] = $nrow;
            }

            if ( $node->children->isNotEmpty() ) {
                $rows = array_merge(
                    $rows,
                    $this->visit( $node->children, $transformer )
                );
            }
        }

        return $rows;
    }


}