<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Cell;

interface IValueBinder
{
    /**
     * Bind value to a cell.
     *
     * @param Cell $cell Cell to bind value to
     * @param mixed $value Value to bind in cell
     */
    public function bindValue(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Cell\Cell $cell, mixed $value) : bool;
}
