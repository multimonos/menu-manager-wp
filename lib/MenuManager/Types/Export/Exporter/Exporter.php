<?php

namespace MenuManager\Types\Export\Exporter;

use MenuManager\Types\Export\ExportConfig;

interface Exporter {
    public function export( ExportConfig $config, array $data ): bool;
}