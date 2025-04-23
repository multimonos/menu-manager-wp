<?php

declare (strict_types=1);
namespace MenuManager\Vendor\ZipStream;

/**
 * @internal
 */
abstract class DataDescriptor
{
    private const SIGNATURE = 0x8074b50;
    public static function generate(int $crc32UncompressedData, int $compressedSize, int $uncompressedSize) : string
    {
        return \MenuManager\Vendor\ZipStream\PackField::pack(new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: self::SIGNATURE), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $crc32UncompressedData), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $compressedSize), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $uncompressedSize));
    }
}
