<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Xlsx;
abstract class WriterPart
{
    /**
     * Parent Xlsx object.
     */
    private Xlsx $parentWriter;
    /**
     * Get parent Xlsx object.
     */
    public function getParentWriter() : Xlsx
    {
        return $this->parentWriter;
    }
    /**
     * Set parent Xlsx object.
     */
    public function __construct(Xlsx $writer)
    {
        $this->parentWriter = $writer;
    }
}
