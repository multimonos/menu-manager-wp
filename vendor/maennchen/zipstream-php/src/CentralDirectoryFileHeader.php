<?php

declare (strict_types=1);
namespace MenuManager\Vendor\ZipStream;

use DateTimeInterface;
/**
 * @internal
 */
abstract class CentralDirectoryFileHeader
{
    private const SIGNATURE = 0x2014b50;
    public static function generate(int $versionMadeBy, int $versionNeededToExtract, int $generalPurposeBitFlag, \MenuManager\Vendor\ZipStream\CompressionMethod $compressionMethod, DateTimeInterface $lastModificationDateTime, int $crc32, int $compressedSize, int $uncompressedSize, string $fileName, string $extraField, string $fileComment, int $diskNumberStart, int $internalFileAttributes, int $externalFileAttributes, int $relativeOffsetOfLocalHeader) : string
    {
        return \MenuManager\Vendor\ZipStream\PackField::pack(new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: self::SIGNATURE), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $versionMadeBy), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $versionNeededToExtract), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $generalPurposeBitFlag), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $compressionMethod->value), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: \MenuManager\Vendor\ZipStream\Time::dateTimeToDosTime($lastModificationDateTime)), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $crc32), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $compressedSize), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $uncompressedSize), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: \strlen($fileName)), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: \strlen($extraField)), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: \strlen($fileComment)), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $diskNumberStart), new \MenuManager\Vendor\ZipStream\PackField(format: 'v', value: $internalFileAttributes), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $externalFileAttributes), new \MenuManager\Vendor\ZipStream\PackField(format: 'V', value: $relativeOffsetOfLocalHeader)) . $fileName . $extraField . $fileComment;
    }
}
