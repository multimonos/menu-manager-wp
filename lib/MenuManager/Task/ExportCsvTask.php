<?php

namespace MenuManager\Task;

use MenuManager\Model\Impex;
use MenuManager\Model\MenuPost;
use MenuManager\Model\Node;
use MenuManager\Service\Database;
use MenuManager\Service\Factory\ExportNodeFactory;
use MenuManager\Types\ExportMethod;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Collection;
use MenuManager\Vendor\League\Csv\AbstractCsv;
use MenuManager\Vendor\League\Csv\Bom;
use MenuManager\Vendor\League\Csv\Writer;
use SplTempFileObject;


class CsvWriterFactory {
    public static function create( ExportMethod $mode, string $path ): AbstractCsv {
        switch ( $mode ) {
            case ExportMethod::Download:
                $writer = Writer::createFromFileObject( new SplTempFileObject() );
                break;

            default:
            case ExportMethod::File:
                $writer = Writer::createFromPath( $path, 'w' );
                break;
        }

        // NOTE
        // User's must import the csv instead of just "opening" the csv, so, that
        // they can choose the UTF8 encoding.
        $writer->setOutputBOM( Bom::Utf8 );

        // Writer config
        $writer->setDelimiter( ',' );
        $writer->setEnclosure( '"' );
        $writer->setEscape( '\\' );
        $writer->setNewline( "\r\n" );
        $writer->forceEnclosure();

        return $writer;
    }
}

class ExportCsvTask {

    public function run( ExportMethod $method, MenuPost $menu, string $path ): TaskResult {

        Database::load()::connection()->enableQueryLog();

        // PAGES
        $expected_count = Node::countForMenu( $menu );
        $page_names = Node::findPageNames( $menu );

        // Collect rows ... write csv.
        $writer = CsvWriterFactory::create( $method, $path );

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

        $queries = Database::load()::connection()->getQueryLog();


        // Download only.
        if ( ExportMethod::Download === $method ) {
            $writer->output( $path );
            exit;
        }

        // Other
        return TaskResult::success( "Exported menu '" . $menu->post->post_name . "' to " . $path, [
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