<?php

namespace MenuManager\Types\Export\Exporter;

use MenuManager\Service\Filesystem;
use MenuManager\Types\Export\ExportConfig;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Spreadsheet;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExportWriter implements Exporter {

    public function export( ExportConfig $config, array $data ): bool {

        $workbook = new Spreadsheet();
        $sheet = $workbook->getActiveSheet();
        $sheet->fromArray( $data, null, 'A1' );
        $writer = new Xlsx( $workbook );
        $writer->save( $config->target );

        return Filesystem::get()->exists( $config->target );
    }
}