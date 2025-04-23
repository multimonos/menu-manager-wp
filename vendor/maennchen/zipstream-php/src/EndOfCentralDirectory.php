<?php

declare (strict_types=1);
namespace MenuManager\Vendor\ZipStream;

/**
 * @internal
 */
abstract class EndOfCentralDirectory
{
    private const SIGNATURE = 0x6054b50;
    public static function generate(int $numberOfThisDisk, int $numberOfTheDiskWithCentralDirectoryStart, int $numberOfCentralDirectoryEntriesOnThisDisk, int $numberOfCentralDirectoryEntries, int $sizeOfCentralDirectory, int $centralDirectoryStartOffsetOnDisk, string $zipFileComment) : string
    {
        /** @psalm-suppress MixedArgument */
        return \MenuManager\Vendor\ZipStream\PackField::pack(new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: static::SIGNATURE), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $numberOfThisDisk), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $numberOfTheDiskWithCentralDirectoryStart), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $numberOfCentralDirectoryEntriesOnThisDisk), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $numberOfCentralDirectoryEntries), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $sizeOfCentralDirectory), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $centralDirectoryStartOffsetOnDisk), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: \strlen($zipFileComment))) . $zipFileComment;
    }
}
