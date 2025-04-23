<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Engineering;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Functions;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\StringHelper;
abstract class ConvertBase
{
    use ArrayEnabled;
    protected static function validateValue(mixed $value) : string
    {
        if (\is_bool($value)) {
            if (Functions::getCompatibilityMode() !== Functions::COMPATIBILITY_OPENOFFICE) {
                throw new Exception(ExcelError::VALUE());
            }
            $value = (int) $value;
        }
        if (\is_numeric($value)) {
            if (Functions::getCompatibilityMode() == Functions::COMPATIBILITY_GNUMERIC) {
                $value = \floor((float) $value);
            }
        }
        return \strtoupper(StringHelper::convertToString($value));
    }
    protected static function validatePlaces(mixed $places = null) : ?int
    {
        if ($places === null) {
            return $places;
        }
        if (\is_numeric($places)) {
            if ($places < 0 || $places > 10) {
                throw new Exception(ExcelError::NAN());
            }
            return (int) $places;
        }
        throw new Exception(ExcelError::VALUE());
    }
    /**
     * Formats a number base string value with leading zeroes.
     *
     * @param string $value The "number" to pad
     * @param ?int $places The length that we want to pad this value
     *
     * @return string The padded "number"
     */
    protected static function nbrConversionFormat(string $value, ?int $places) : string
    {
        if ($places !== null) {
            if (\strlen($value) <= $places) {
                return \substr(\str_pad($value, $places, '0', \STR_PAD_LEFT), -10);
            }
            return ExcelError::NAN();
        }
        return \substr($value, -10);
    }
}
