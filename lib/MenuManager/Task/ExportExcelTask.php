<?php

namespace MenuManager\Task;

use MenuManager\Database\db;
use MenuManager\Database\Factory\ExportNodeFactory;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Node;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Collection;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Spreadsheet;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportExcelTask {

    public function run( \WP_Post $menu, string $path ): TaskResult {

        db::load()::connection()->enableQueryLog();


        // PAGES
        $page_names = Node::findPageNames( $menu );

        if ( empty( $page_names ) ) {
            return TaskResult::failure( "No pages found for menu '{$menu}." );
        }

        // DATA
        $rows = [Impex::CSV_FIELDS];

        foreach ( $page_names as $page_name ) {

            // new
            $page = Node::findPageNode( $menu, $page_name );
            $tree = Node::getSortedMenu( $menu, $page );

            $sparse_rows = $this->visit( $tree, fn( Node $node ) => ExportNodeFactory::createRow( $menu, $page_name, $node ) );

            foreach ( $sparse_rows as $sparse_row ) {
                $dense_row = self::arrayFillKeys( Impex::CSV_FIELDS, $sparse_row );
                $rows[] = $dense_row;
            }
        }

        // WORKBOOK
        $workbook = new Spreadsheet();
        $sheet = $workbook->getActiveSheet();
        $sheet->fromArray( $rows, null, 'A1' );
        $writer = new Xlsx( $workbook );
        $writer->save( $path );

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