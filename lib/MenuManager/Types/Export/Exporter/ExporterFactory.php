<?php

namespace MenuManager\Types\Export\Exporter;

use MenuManager\Types\Export\ExportConfig;
use MenuManager\Types\Export\ExportContext;
use MenuManager\Types\Export\ExportFormat;

class ExporterFactory {
    private const EXPORT_MAP = [
        ExportContext::Cli->value      => [
            ExportFormat::Csv->value   => CsvExportWriter::class,
            ExportFormat::Excel->value => ExcelExportWriter::class,
        ],
        ExportContext::Download->value => [
            ExportFormat::Csv->value   => CsvExportDownloader::class,
            ExportFormat::Excel->value => ExcelExportDownloader::class,
        ],
    ];

    public static function create( ExportConfig $config ): Exporter {
        $class = self::EXPORT_MAP[$config->context->value][$config->format->value] ?? ExporterStub::class;
        return new $class();
    }
}