<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Collection;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Settings;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
abstract class CellsFactory
{
    /**
     * Initialise the cache storage.
     *
     * @param Worksheet $worksheet Enable cell caching for this worksheet
     *
     * */
    public static function getInstance(Worksheet $worksheet) : \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Collection\Cells
    {
        return new \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Collection\Cells($worksheet, Settings::getCache());
    }
}
