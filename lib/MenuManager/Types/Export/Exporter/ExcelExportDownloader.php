<?php

namespace MenuManager\Types\Export\Exporter;

use MenuManager\Types\Export\ExportConfig;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Spreadsheet;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExportDownloader implements Exporter {

    public function export( ExportConfig $config, array $data ): bool {
        // Create
        $workbook = new Spreadsheet();
        $sheet = $workbook->getActiveSheet();
        $sheet->fromArray( $data, null, 'A1' );
        $writer = new Xlsx( $workbook );
        $writer->save( $config->target );

        // Send headers.
        header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
        header( 'Content-Disposition: attachment; filename="' . $config->target . '"' );
        header( 'Cache-Control: max-age=0' );

        // If you're serving to IE over HTTPS, remove the Cache-Control header
        header( 'Cache-Control: max-age=1' );

        // If you're serving to IE
        header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' ); // Date in the past
        header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
        header( 'Cache-Control: cache, must-revalidate' );
        header( 'Pragma: public' );

        $writer->save( 'php://output' );
        exit;

        return true;
    }
}