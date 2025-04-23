<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Ods;

class Mimetype extends \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Ods\WriterPart
{
    /**
     * Write mimetype to plain text format.
     *
     * @return string XML Output
     */
    public function write() : string
    {
        return 'application/vnd.oasis.opendocument.spreadsheet';
    }
}
