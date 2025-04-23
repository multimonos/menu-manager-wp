<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception;
class Sqrt
{
    use ArrayEnabled;
    /**
     * SQRT.
     *
     * Returns the result of builtin function sqrt after validating args.
     *
     * @param mixed $number Should be numeric, or can be an array of numbers
     *
     * @return array|float|string square root
     *         If an array of numbers is passed as the argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function sqrt(mixed $number)
    {
        if (\is_array($number)) {
            return self::evaluateSingleArgumentArray([self::class, __FUNCTION__], $number);
        }
        try {
            $number = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullBool($number);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::numberOrNan(\sqrt($number));
    }
    /**
     * SQRTPI.
     *
     * Returns the square root of (number * pi).
     *
     * @param array|float $number Number, or can be an array of numbers
     *
     * @return array|float|string Square Root of Number * Pi, or a string containing an error
     *         If an array of numbers is passed as the argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function pi($number) : array|string|float
    {
        if (\is_array($number)) {
            return self::evaluateSingleArgumentArray([self::class, __FUNCTION__], $number);
        }
        try {
            $number = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullSubstitution($number, 0);
            \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNotNegative($number);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return \sqrt($number * \M_PI);
    }
}
