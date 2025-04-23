<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Worksheet;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Cell\CellAddress;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Cell\CellRange;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Cell\Coordinate;
class AutoFit
{
    protected \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet;
    public function __construct(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet)
    {
        $this->worksheet = $worksheet;
    }
    public function getAutoFilterIndentRanges() : array
    {
        $autoFilterIndentRanges = [];
        $autoFilterIndentRanges[] = $this->getAutoFilterIndentRange($this->worksheet->getAutoFilter());
        foreach ($this->worksheet->getTableCollection() as $table) {
            /** @var Table $table */
            if ($table->getShowHeaderRow() === \true && $table->getAllowFilter() === \true) {
                $autoFilter = $table->getAutoFilter();
                $autoFilterIndentRanges[] = $this->getAutoFilterIndentRange($autoFilter);
            }
        }
        return \array_filter($autoFilterIndentRanges);
    }
    private function getAutoFilterIndentRange(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter $autoFilter) : ?string
    {
        $autoFilterRange = $autoFilter->getRange();
        $autoFilterIndentRange = null;
        if (!empty($autoFilterRange)) {
            $autoFilterRangeBoundaries = Coordinate::rangeBoundaries($autoFilterRange);
            $autoFilterIndentRange = (string) new CellRange(CellAddress::fromColumnAndRow($autoFilterRangeBoundaries[0][0], $autoFilterRangeBoundaries[0][1]), CellAddress::fromColumnAndRow($autoFilterRangeBoundaries[1][0], $autoFilterRangeBoundaries[0][1]));
        }
        return $autoFilterIndentRange;
    }
}
