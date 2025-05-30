<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Functions;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
class Deviations
{
    /**
     * DEVSQ.
     *
     * Returns the sum of squares of deviations of data points from their sample mean.
     *
     * Excel Function:
     *        DEVSQ(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
     */
    public static function sumSquares(mixed ...$args) : string|float
    {
        $aArgs = Functions::flattenArrayIndexed($args);
        $aMean = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\Averages::average($aArgs);
        if (!\is_numeric($aMean)) {
            return ExcelError::NAN();
        }
        // Return value
        $returnValue = 0.0;
        $aCount = -1;
        foreach ($aArgs as $k => $arg) {
            // Is it a numeric value?
            if (\is_bool($arg) && (!Functions::isCellValue($k) || Functions::getCompatibilityMode() == Functions::COMPATIBILITY_OPENOFFICE)) {
                $arg = (int) $arg;
            }
            if (\is_numeric($arg) && !\is_string($arg)) {
                $returnValue += ($arg - $aMean) ** 2;
                ++$aCount;
            }
        }
        return $aCount === 0 ? ExcelError::VALUE() : $returnValue;
    }
    /**
     * KURT.
     *
     * Returns the kurtosis of a data set. Kurtosis characterizes the relative peakedness
     * or flatness of a distribution compared with the normal distribution. Positive
     * kurtosis indicates a relatively peaked distribution. Negative kurtosis indicates a
     * relatively flat distribution.
     *
     * @param array ...$args Data Series
     */
    public static function kurtosis(...$args) : string|int|float
    {
        $aArgs = Functions::flattenArrayIndexed($args);
        $mean = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\Averages::average($aArgs);
        if (!\is_numeric($mean)) {
            return ExcelError::DIV0();
        }
        $stdDev = (float) \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\StandardDeviations::STDEV($aArgs);
        if ($stdDev > 0) {
            $count = $summer = 0;
            foreach ($aArgs as $k => $arg) {
                if (\is_bool($arg) && !Functions::isMatrixValue($k)) {
                } else {
                    // Is it a numeric value?
                    if (\is_numeric($arg) && !\is_string($arg)) {
                        $summer += (($arg - $mean) / $stdDev) ** 4;
                        ++$count;
                    }
                }
            }
            if ($count > 3) {
                return $summer * ($count * ($count + 1) / (($count - 1) * ($count - 2) * ($count - 3))) - 3 * ($count - 1) ** 2 / (($count - 2) * ($count - 3));
            }
        }
        return ExcelError::DIV0();
    }
    /**
     * SKEW.
     *
     * Returns the skewness of a distribution. Skewness characterizes the degree of asymmetry
     * of a distribution around its mean. Positive skewness indicates a distribution with an
     * asymmetric tail extending toward more positive values. Negative skewness indicates a
     * distribution with an asymmetric tail extending toward more negative values.
     *
     * @param array ...$args Data Series
     *
     * @return float|int|string The result, or a string containing an error
     */
    public static function skew(...$args) : string|int|float
    {
        $aArgs = Functions::flattenArrayIndexed($args);
        $mean = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\Averages::average($aArgs);
        if (!\is_numeric($mean)) {
            return ExcelError::DIV0();
        }
        $stdDev = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\StandardDeviations::STDEV($aArgs);
        if ($stdDev === 0.0 || \is_string($stdDev)) {
            return ExcelError::DIV0();
        }
        $count = $summer = 0;
        // Loop through arguments
        foreach ($aArgs as $k => $arg) {
            if (\is_bool($arg) && !Functions::isMatrixValue($k)) {
            } elseif (!\is_numeric($arg)) {
                return ExcelError::VALUE();
            } else {
                // Is it a numeric value?
                if (!\is_string($arg)) {
                    $summer += (($arg - $mean) / $stdDev) ** 3;
                    ++$count;
                }
            }
        }
        if ($count > 2) {
            return $summer * ($count / (($count - 1) * ($count - 2)));
        }
        return ExcelError::DIV0();
    }
}
