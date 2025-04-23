<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Functions;
class ErrorValue
{
    use ArrayEnabled;
    /**
     * IS_ERR.
     *
     * @param mixed $value Value to check
     *                      Or can be an array of values
     *
     * @return array|bool If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function isErr(mixed $value = '') : array|bool
    {
        if (\is_array($value)) {
            return self::evaluateSingleArgumentArray([self::class, __FUNCTION__], $value);
        }
        return self::isError($value) && !self::isNa($value);
    }
    /**
     * IS_ERROR.
     *
     * @param mixed $value Value to check
     *                      Or can be an array of values
     *
     * @return array|bool If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function isError(mixed $value = '', bool $tryNotImplemented = \false) : array|bool
    {
        if (\is_array($value)) {
            return self::evaluateSingleArgumentArray([self::class, __FUNCTION__], $value);
        }
        if (!\is_string($value)) {
            return \false;
        }
        if ($tryNotImplemented && $value === Functions::NOT_YET_IMPLEMENTED) {
            return \true;
        }
        return \in_array($value, \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError::ERROR_CODES, \true);
    }
    /**
     * IS_NA.
     *
     * @param mixed $value Value to check
     *                      Or can be an array of values
     *
     * @return array|bool If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function isNa(mixed $value = '') : array|bool
    {
        if (\is_array($value)) {
            return self::evaluateSingleArgumentArray([self::class, __FUNCTION__], $value);
        }
        return $value === \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError::NA();
    }
}
