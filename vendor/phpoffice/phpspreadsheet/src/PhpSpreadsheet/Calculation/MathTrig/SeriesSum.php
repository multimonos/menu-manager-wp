<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Functions;
class SeriesSum
{
    use ArrayEnabled;
    /**
     * SERIESSUM.
     *
     * Returns the sum of a power series
     *
     * @param mixed $x Input value
     * @param mixed $n Initial power
     * @param mixed $m Step
     * @param mixed[] $args An array of coefficients for the Data Series
     *
     * @return array|float|int|string The result, or a string containing an error
     */
    public static function evaluate(mixed $x, mixed $n, mixed $m, ...$args) : array|string|float|int
    {
        if (\is_array($x) || \is_array($n) || \is_array($m)) {
            return self::evaluateArrayArgumentsSubset([self::class, __FUNCTION__], 3, $x, $n, $m, ...$args);
        }
        try {
            $x = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullSubstitution($x, 0);
            $n = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullSubstitution($n, 0);
            $m = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullSubstitution($m, 0);
            // Loop through arguments
            $aArgs = Functions::flattenArray($args);
            $returnValue = 0;
            $i = 0;
            foreach ($aArgs as $argx) {
                if ($argx !== null) {
                    $arg = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Helpers::validateNumericNullSubstitution($argx, 0);
                    $returnValue += $arg * $x ** ($n + $m * $i);
                    ++$i;
                }
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $returnValue;
    }
}
