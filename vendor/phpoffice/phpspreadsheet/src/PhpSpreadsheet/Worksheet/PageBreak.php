<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Worksheet;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Functions;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Cell\CellAddress;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Cell\Coordinate;
class PageBreak
{
    private int $breakType;
    private string $coordinate;
    private int $maxColOrRow;
    /**
     * @param array{0: int, 1: int}|CellAddress|string $coordinate
     */
    public function __construct(int $breakType, CellAddress|string|array $coordinate, int $maxColOrRow = -1)
    {
        $coordinate = Functions::trimSheetFromCellReference(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Worksheet\Validations::validateCellAddress($coordinate));
        $this->breakType = $breakType;
        $this->coordinate = $coordinate;
        $this->maxColOrRow = $maxColOrRow;
    }
    public function getBreakType() : int
    {
        return $this->breakType;
    }
    public function getCoordinate() : string
    {
        return $this->coordinate;
    }
    public function getMaxColOrRow() : int
    {
        return $this->maxColOrRow;
    }
    public function getColumnInt() : int
    {
        return Coordinate::indexesFromString($this->coordinate)[0];
    }
    public function getRow() : int
    {
        return Coordinate::indexesFromString($this->coordinate)[1];
    }
    public function getColumnString() : string
    {
        return Coordinate::indexesFromString($this->coordinate)[2];
    }
}
