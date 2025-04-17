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

        db::load();
        db::load()::connection()->enableQueryLog();

        // PAGES
        $expected_count = Node::countForMenu( $menu );
        $pages = Node::findPageNames( $menu );

        // Collect rows ... write csv.
//        echo "\n";

        // writer
        $writer = Writer::createFromPath( $path, 'w' );
//        $writer->setOutputBOM( Writer::BOM_UTF8 ); // Add BOM for UTF-8
        $writer->setOutputBOM( Bom::Utf8 );
//        $writer->setOutputBOM( Bom::Utf16Le );
//        $writer->setEnclosure( '"' );
        $writer->forceEnclosure();

        // headings
        $writer->insertOne( Impex::CSV_FIELDS );

        //  rows
        if ( ! empty( $pages ) ) {

            foreach ( $pages as $page ) {
                $tree = Node::findPageTree( $menu, $page );

                $rows = $this->visit( $tree, fn( Node $node ) => ExportNodeFactory::createRow( $menu, $page, $node ) );

                foreach ( $rows as $sparse_row ) {
                    $dense_row = self::arrayFillKeys( Impex::CSV_FIELDS, $sparse_row );
                    $writer->insertOne( $dense_row );
                }
            }
        }

        $queries = db::load()::connection()->getQueryLog();

//        echo "\n" . count( $queries ) . ' queries';
//        echo "\nsource.count: " . $expected_count;
//        echo "\nexport.count: " . count( $rows );
//        echo "\n";


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

//        echo "\n" . count( $rows );
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