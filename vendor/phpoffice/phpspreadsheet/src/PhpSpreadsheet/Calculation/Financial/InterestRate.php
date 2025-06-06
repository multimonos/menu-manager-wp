<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Functions;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
class InterestRate
{
    /**
     * EFFECT.
     *
     * Returns the effective interest rate given the nominal rate and the number of
     *        compounding payments per year.
     *
     * Excel Function:
     *        EFFECT(nominal_rate,npery)
     *
     * @param mixed $nominalRate Nominal interest rate as a float
     * @param mixed $periodsPerYear Integer number of compounding payments per year
     */
    public static function effective(mixed $nominalRate = 0, mixed $periodsPerYear = 0) : string|float
    {
        $nominalRate = Functions::flattenSingleValue($nominalRate);
        $periodsPerYear = Functions::flattenSingleValue($periodsPerYear);
        try {
            $nominalRate = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\FinancialValidations::validateFloat($nominalRate);
            $periodsPerYear = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\FinancialValidations::validateInt($periodsPerYear);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($nominalRate <= 0 || $periodsPerYear < 1) {
            return ExcelError::NAN();
        }
        return (1 + $nominalRate / $periodsPerYear) ** $periodsPerYear - 1;
    }
    /**
     * NOMINAL.
     *
     * Returns the nominal interest rate given the effective rate and the number of compounding payments per year.
     *
     * @param mixed $effectiveRate Effective interest rate as a float
     * @param mixed $periodsPerYear Integer number of compounding payments per year
     *
     * @return float|string Result, or a string containing an error
     */
    public static function nominal(mixed $effectiveRate = 0, mixed $periodsPerYear = 0) : string|float
    {
        $effectiveRate = Functions::flattenSingleValue($effectiveRate);
        $periodsPerYear = Functions::flattenSingleValue($periodsPerYear);
        try {
            $effectiveRate = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\FinancialValidations::validateFloat($effectiveRate);
            $periodsPerYear = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\FinancialValidations::validateInt($periodsPerYear);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($effectiveRate <= 0 || $periodsPerYear < 1) {
            return ExcelError::NAN();
        }
        // Calculate
        return $periodsPerYear * (($effectiveRate + 1) ** (1 / $periodsPerYear) - 1);
    }
}
