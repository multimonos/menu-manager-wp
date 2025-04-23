<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Ods;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Ods;
abstract class WriterPart
{
    /**
     * Parent Ods object.
     */
    private Ods $parentWriter;
    /**
     * Get Ods writer.
     */
    public function getParentWriter() : Ods
    {
        return $this->parentWriter;
    }
    /**
     * Set parent Ods writer.
     */
    public function __construct(Ods $writer)
    {
        $this->parentWriter = $writer;
    }
    public abstract function write() : string;
}
