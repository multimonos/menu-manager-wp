<?php

namespace MenuManager\Types\Export\Exporter;

use MenuManager\Types\Export\ExportConfig;

class ExporterStub implements Exporter {

    public function export( ExportConfig $config, array $data ): bool {
        return true;
    }
}