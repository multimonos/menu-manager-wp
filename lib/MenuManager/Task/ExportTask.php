<?php

namespace MenuManager\Task;

use MenuManager\Database\db;
use MenuManager\Database\Factory\ExportNodeFactory;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Node;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Collection;
use MenuManager\Vendor\League\Csv\Bom;
use MenuManager\Vendor\League\Csv\Writer;

class ExportTask {

    public function run( \WP_Post $menu, string $path ): TaskResult {

        db::load()::connection()->enableQueryLog();

        // PAGES
        $expected_count = Node::countForMenu( $menu );
        $page_names = Node::findPageNames( $menu );

        // Collect rows ... write csv.
        $writer = Writer::createFromPath( $path, 'w' );

        // NOTE: User's must import the csv instead of just "opening" the csv, so, that they can choose the UTF8 encoding.
        $writer->setOutputBOM( Bom::Utf8 );

        // Writer config
        $writer->setDelimiter( ',' );
        $writer->setEnclosure( '"' );
        $writer->setEscape( '\\' );
        $writer->setNewline( "\r\n" );
        $writer->forceEnclosure();


        // headings
        $writer->insertOne( Impex::CSV_FIELDS );

        //  rows
        if ( ! empty( $page_names ) ) {

            foreach ( $page_names as $page_name ) {

                // new
                $page = Node::findPageNode( $menu, $page_name );
                $tree = Node::getSortedMenu( $menu, $page );

                $rows = $this->visit( $tree, fn( Node $node ) => ExportNodeFactory::createRow( $menu, $page_name, $node ) );

                foreach ( $rows as $sparse_row ) {
                    $dense_row = self::arrayFillKeys( Impex::CSV_FIELDS, $sparse_row );
                    $writer->insertOne( $dense_row );
                }
            }
        }

        $queries = db::load()::connection()->getQueryLog();

        return TaskResult::success( "Exported menu '" . $menu->post_name . "' to " . $path, [
            'queries' => count( $queries ) . ' queries',
        ] );
    }

    protected function visit( Collection $nodes, callable $callback ): array {

        $rows = [];

        foreach ( $nodes as $node ) {
            $nrow = $callback( $node );

            if ( ! is_null( $nrow ) ) {
                $rows[] = $nrow;
            }

            if ( $node->children->isNotEmpty() ) {
                $rows = array_merge(
                    $rows,
                    $this->visit( $node->children, $callback )
                );
            }
        }

        return $rows;
    }


    public static function arrayFillKeys( array $keys, array $data, $fill_value = null ): array {
        $rs = [];
        foreach ( $keys as $k ) {
            $rs[$k] = $data[$k] ?? $fill_value;
        }
        return $rs;
    }
}