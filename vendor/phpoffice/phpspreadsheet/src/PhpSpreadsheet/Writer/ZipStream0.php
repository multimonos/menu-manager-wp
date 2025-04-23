<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer;

use MenuManager\Vendor\ZipStream\Option\Archive;
use MenuManager\Vendor\ZipStream\ZipStream;
class ZipStream0
{
    /**
     * @param resource $fileHandle
     */
    public static function newZipStream($fileHandle) : ZipStream
    {
        return \class_exists(Archive::class) ? \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\ZipStream2::newZipStream($fileHandle) : \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\ZipStream3::newZipStream($fileHandle);
    }
}
