<?php

declare (strict_types=1);
namespace MenuManager\Vendor\ZipStream;

use DateTimeInterface;
/**
 * @internal
 */
abstract class LocalFileHeader
{
    private const SIGNATURE = 0x4034b50;
    public static function generate(int $versionNeededToExtract, int $generalPurposeBitFlag, \MenuManager\Vendor\ZipStream\CompressionMethod $compressionMethod, DateTimeInterface $lastModificationDateTime, int $crc32UncompressedData, int $compressedSize, int $uncompressedSize, string $fileName, string $extraField) : string
    {
        return \MenuManager\Vendor\ZipStream\PackField::pack(new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: self::SIGNATURE), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $versionNeededToExtract), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $generalPurposeBitFlag), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $compressionMethod->value), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: \MenuManager\Vendor\ZipStream\Time::dateTimeToDosTime($lastModificationDateTime)), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $crc32UncompressedData), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $compressedSize), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $uncompressedSize), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: \strlen($fileName)), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: \strlen($extraField))) . $fileName . $extraField;
    }
}
