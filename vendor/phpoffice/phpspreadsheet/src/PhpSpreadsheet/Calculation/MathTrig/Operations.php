<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Functions;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
class Operations
{
    use ArrayEnabled;
    /**
     * MOD.
     *
     * @param mixed $dividend Dividend
     *                      Or can be an array of values
     * @param mixed $divisor Divisor
     *                      Or can be an array of values
     *
     * @return array|float|string Remainder, or a string containing an error
     *         If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function mod(mixed $dividend, mixed $divisor) : array|string|float
    {
        if (\is_array($dividend) || \is_array($divisor)) {
            return self::evaluateArrayArguments([self::class, __FUNCTION__], $dividend, $divisor);
        }
        try {
            $dividend = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullBool($dividend);
            $divisor = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullBool($divisor);
            \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNotZero($divisor);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($dividend < 0.0 && $divisor > 0.0) {
            return $divisor - \fmod(\abs($dividend), $divisor);
        }
        if ($dividend > 0.0 && $divisor < 0.0) {
            return $divisor + \fmod($dividend, \abs($divisor));
        }
        return \fmod($dividend, $divisor);
    }
    /**
     * POWER.
     *
     * Computes x raised to the power y.
     *
     * @param null|array|bool|float|int|string $x Or can be an array of values
     * @param null|array|bool|float|int|string $y Or can be an array of values
     *
     * @return array|float|int|string The result, or a string containing an error
     *         If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function power(null|array|bool|float|int|string $x, null|array|bool|float|int|string $y) : array|float|int|string
    {
        if (\is_array($x) || \is_array($y)) {
            return self::evaluateArrayArguments([self::class, __FUNCTION__], $x, $y);
        }
        try {
            $x = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullBool($x);
            $y = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullBool($y);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        // Validate parameters
        if (!$x && !$y) {
            return ExcelError::NAN();
        }
        if (!$x && $y < 0.0) {
            return ExcelError::DIV0();
        }
        // Return
        $result = $x ** $y;
        return \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::numberOrNan($result);
    }
    /**
     * PRODUCT.
     *
     * PRODUCT returns the product of all the values and cells referenced in the argument list.
     *
     * Excel Function:
     *        PRODUCT(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
     */
    public static function product(mixed ...$args) : string|float
    {
        $args = \array_filter(Functions::flattenArray($args), fn($value): bool => $value !== null);
        // Return value
        $returnValue = \count($args) === 0 ? 0.0 : 1.0;
        // Loop through arguments
        foreach ($args as $arg) {
            // Is it a numeric value?
            if (\is_numeric($arg)) {
                $returnValue *= $arg;
            } else {
                return ExcelError::throwError($arg);
            }
        }
        return (float) $returnValue;
    }
    /**
     * QUOTIENT.
     *
     * QUOTIENT function returns the integer portion of a division. Numerator is the divided number
     *        and denominator is the divisor.
     *
     * Excel Function:
     *        QUOTIENT(value1,value2)
     *
     * @param mixed $numerator Expect float|int
     *                      Or can be an array of values
     * @param mixed $denominator Expect float|int
     *                      Or can be an array of values
     *
     * @return array|int|string If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function quotient(mixed $numerator, mixed $denominator) : array|string|int
    {
        if (\is_array($numerator) || \is_array($denominator)) {
            return self::evaluateArrayArguments([self::class, __FUNCTION__], $numerator, $denominator);
        }
        try {
            $numerator = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullSubstitution($numerator, 0);
            $denominator = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullSubstitution($denominator, 0);
            \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNotZero($denominator);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return (int) ($numerator / $denominator);
    }
}
