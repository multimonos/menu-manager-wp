<?php

namespace MenuManager\Types\Export\Exporter;

use MenuManager\Service\Filesystem;
use MenuManager\Types\Export\ExportConfig;
use MenuManager\Vendor\League\Csv\Bom;
use MenuManager\Vendor\League\Csv\Writer;

class CsvExportWriter implements Exporter {
    public function export( ExportConfig $config, array $data ): bool {

        // Create
        $writer = Writer::createFromPath( $config->target, 'w' );

        // NOTE
        // User's must import the csv instead of just "opening" the csv, so, that
        // they can choose the UTF8 encoding.
        $writer->setOutputBOM( Bom::Utf8 );

        // Config
        $writer->setDelimiter( ',' );
        $writer->setEnclosure( '"' );
        $writer->setEscape( '\\' );
        $writer->setNewline( "\r\n" );
        $writer->forceEnclosure();

        // Write
        $writer->insertAll( $data );

        // Validate
        return Filesystem::get()->exists( $config->target );
    }
}