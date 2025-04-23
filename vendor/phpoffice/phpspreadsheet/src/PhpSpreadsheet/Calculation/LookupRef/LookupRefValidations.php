<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\LookupRef;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ErrorValue;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
class LookupRefValidations
{
    public static function validateInt(mixed $value) : int
    {
        if (!\is_numeric($value)) {
            if (\is_string($value) && ErrorValue::isError($value)) {
                throw new Exception($value);
            }
            throw new Exception(ExcelError::VALUE());
        }
        return (int) \floor((float) $value);
    }
    public static function validatePositiveInt(mixed $value, bool $allowZero = \true) : int
    {
        $value = self::validateInt($value);
        if ($allowZero === \false && $value <= 0 || $value < 0) {
            throw new Exception(ExcelError::VALUE());
        }
        return $value;
    }
}
