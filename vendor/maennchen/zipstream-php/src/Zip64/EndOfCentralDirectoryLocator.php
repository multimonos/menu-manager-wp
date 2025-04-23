<?php

declare (strict_types=1);
namespace MenuManager\Vendor\ZipStream\Zip64;

use MenuManager\Vendor\ZipStream\PackField;
/**
 * @internal
 */
abstract class EndOfCentralDirectoryLocator
{
    private const SIGNATURE = 0x7064b50;
    public static function generate(int $numberOfTheDiskWithZip64CentralDirectoryStart, int $zip64centralDirectoryStartOffsetOnDisk, int $totalNumberOfDisks) : string
    {
        /** @psalm-suppress MixedArgument */
        return PackField::pack(new PackField(format: 'V', value: static::SIGNATURE), new PackField(format: 'V', value: $numberOfTheDiskWithZip64CentralDirectoryStart), new PackField(format: 'P', value: $zip64centralDirectoryStartOffsetOnDisk), new PackField(format: 'V', value: $totalNumberOfDisks));
    }
}
