<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception;
class Sign
{
    use ArrayEnabled;
    /**
     * SIGN.
     *
     * Determines the sign of a number. Returns 1 if the number is positive, zero (0)
     *        if the number is 0, and -1 if the number is negative.
     *
     * @param array|float $number Number to round, or can be an array of numbers
     *
     * @return array|int|string sign value, or a string containing an error
     *         If an array of numbers is passed as the argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function evaluate($number) : array|string|int
    {
        if (\is_array($number)) {
            return self::evaluateSingleArgumentArray([self::class, __FUNCTION__], $number);
        }
        try {
            $number = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullBool($number);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::returnSign($number);
    }
}
