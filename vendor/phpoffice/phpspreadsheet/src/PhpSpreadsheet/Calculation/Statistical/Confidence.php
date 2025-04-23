<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Functions;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
class Confidence
{
    use ArrayEnabled;
    /**
     * CONFIDENCE.
     *
     * Returns the confidence interval for a population mean
     *
     * @param mixed $alpha As a float
     *                      Or can be an array of values
     * @param mixed $stdDev Standard Deviation as a float
     *                      Or can be an array of values
     * @param mixed $size As an integer
     *                      Or can be an array of values
     *
     * @return array|float|string If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function CONFIDENCE(mixed $alpha, mixed $stdDev, mixed $size)
    {
        if (\is_array($alpha) || \is_array($stdDev) || \is_array($size)) {
            return self::evaluateArrayArguments([self::class, __FUNCTION__], $alpha, $stdDev, $size);
        }
        try {
            $alpha = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\StatisticalValidations::validateFloat($alpha);
            $stdDev = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\StatisticalValidations::validateFloat($stdDev);
            $size = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\StatisticalValidations::validateInt($size);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($alpha <= 0 || $alpha >= 1 || $stdDev <= 0 || $size < 1) {
            return ExcelError::NAN();
        }
        /** @var float $temp */
        $temp = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\StandardNormal::inverse(1 - $alpha / 2);
        /** @var float */
        $result = Functions::scalar($temp * $stdDev / \sqrt($size));
        return $result;
    }
}
