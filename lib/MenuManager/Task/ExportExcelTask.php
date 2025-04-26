<?php

namespace MenuManager\Task;

use MenuManager\Model\Impex;
use MenuManager\Model\Node;
use MenuManager\Service\Database;
use MenuManager\Service\Factory\ExportNodeFactory;
use MenuManager\Types\ExportMethod;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Collection;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Spreadsheet;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportExcelTask {

    public function run( ExportMethod $method, \WP_Post $menu, string $path ): TaskResult {

        Database::load()::connection()->enableQueryLog();

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


        // DOWNLOAD
        if ( ExportMethod::Download === $method ) {
            header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
            header( 'Content-Disposition: attachment; filename="' . $path . '"' );
            header( 'Cache-Control: max-age=0' );

            // If you're serving to IE over HTTPS, remove the Cache-Control header
            header( 'Cache-Control: max-age=1' );

            // If you're serving to IE
            header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' ); // Date in the past
            header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
            header( 'Cache-Control: cache, must-revalidate' );
            header( 'Pragma: public' );

            // Write to php://output
            $writer->save( 'php://output' );
            exit;
        }

        $queries = Database::load()::connection()->getQueryLog();

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