<?php

namespace MenuManager\Types\Export;

enum ExportFormat: string {
    case Csv = 'csv';
    case Excel = 'excel';

    public function ext() {
        return match ($this) {
            ExportFormat::Csv => '.csv',
            ExportFormat::Excel => '.xlsx',
        };
    }
}