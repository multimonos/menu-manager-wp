<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Constants as FinancialConstants;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Coupons;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Helpers;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Functions;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\StringHelper;
class Price
{
    /**
     * PRICE.
     *
     * Returns the price per $100 face value of a security that pays periodic interest.
     *
     * @param mixed $settlement The security's settlement date.
     *                              The security settlement date is the date after the issue date when the security
     *                              is traded to the buyer.
     * @param mixed $maturity The security's maturity date.
     *                                The maturity date is the date when the security expires.
     * @param mixed $rate the security's annual coupon rate
     * @param mixed $yield the security's annual yield
     * @param mixed $redemption The number of coupon payments per year.
     *                              For annual payments, frequency = 1;
     *                              for semiannual, frequency = 2;
     *                              for quarterly, frequency = 4.
     * @param mixed $basis The type of day count to use.
     *                         0 or omitted    US (NASD) 30/360
     *                         1               Actual/actual
     *                         2               Actual/360
     *                         3               Actual/365
     *                         4               European 30/360
     *
     * @return float|string Result, or a string containing an error
     */
    public static function price(mixed $settlement, mixed $maturity, mixed $rate, mixed $yield, mixed $redemption, mixed $frequency, mixed $basis = FinancialConstants::BASIS_DAYS_PER_YEAR_NASD) : string|float
    {
        $settlement = Functions::flattenSingleValue($settlement);
        $maturity = Functions::flattenSingleValue($maturity);
        $rate = Functions::flattenSingleValue($rate);
        $yield = Functions::flattenSingleValue($yield);
        $redemption = Functions::flattenSingleValue($redemption);
        $frequency = Functions::flattenSingleValue($frequency);
        $basis = $basis === null ? FinancialConstants::BASIS_DAYS_PER_YEAR_NASD : Functions::flattenSingleValue($basis);
        try {
            $settlement = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateSettlementDate($settlement);
            $maturity = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateMaturityDate($maturity);
            \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateSecurityPeriod($settlement, $maturity);
            $rate = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateRate($rate);
            $yield = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateYield($yield);
            $redemption = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateRedemption($redemption);
            $frequency = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateFrequency($frequency);
            $basis = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateBasis($basis);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        $dsc = (float) Coupons::COUPDAYSNC($settlement, $maturity, $frequency, $basis);
        $e = (float) Coupons::COUPDAYS($settlement, $maturity, $frequency, $basis);
        $n = (int) Coupons::COUPNUM($settlement, $maturity, $frequency, $basis);
        $a = (float) Coupons::COUPDAYBS($settlement, $maturity, $frequency, $basis);
        $baseYF = 1.0 + $yield / $frequency;
        $rfp = 100 * ($rate / $frequency);
        $de = $dsc / $e;
        $result = $redemption / $baseYF ** (--$n + $de);
        for ($k = 0; $k <= $n; ++$k) {
            $result += $rfp / $baseYF ** ($k + $de);
        }
        $result -= $rfp * ($a / $e);
        return $result;
    }
    /**
     * PRICEDISC.
     *
     * Returns the price per $100 face value of a discounted security.
     *
     * @param mixed $settlement The security's settlement date.
     *                              The security settlement date is the date after the issue date when the security
     *                              is traded to the buyer.
     * @param mixed $maturity The security's maturity date.
     *                                The maturity date is the date when the security expires.
     * @param mixed $discount The security's discount rate
     * @param mixed $redemption The security's redemption value per $100 face value
     * @param mixed $basis The type of day count to use.
     *                         0 or omitted    US (NASD) 30/360
     *                         1               Actual/actual
     *                         2               Actual/360
     *                         3               Actual/365
     *                         4               European 30/360
     *
     * @return float|string Result, or a string containing an error
     */
    public static function priceDiscounted(mixed $settlement, mixed $maturity, mixed $discount, mixed $redemption, mixed $basis = FinancialConstants::BASIS_DAYS_PER_YEAR_NASD)
    {
        $settlement = Functions::flattenSingleValue($settlement);
        $maturity = Functions::flattenSingleValue($maturity);
        $discount = Functions::flattenSingleValue($discount);
        $redemption = Functions::flattenSingleValue($redemption);
        $basis = $basis === null ? FinancialConstants::BASIS_DAYS_PER_YEAR_NASD : Functions::flattenSingleValue($basis);
        try {
            $settlement = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateSettlementDate($settlement);
            $maturity = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateMaturityDate($maturity);
            \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateSecurityPeriod($settlement, $maturity);
            $discount = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateDiscount($discount);
            $redemption = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateRedemption($redemption);
            $basis = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateBasis($basis);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        $daysBetweenSettlementAndMaturity = Functions::scalar(DateTimeExcel\YearFrac::fraction($settlement, $maturity, $basis));
        if (!\is_numeric($daysBetweenSettlementAndMaturity)) {
            //    return date error
            return StringHelper::convertToString($daysBetweenSettlementAndMaturity);
        }
        return $redemption * (1 - $discount * $daysBetweenSettlementAndMaturity);
    }
    /**
     * PRICEMAT.
     *
     * Returns the price per $100 face value of a security that pays interest at maturity.
     *
     * @param mixed $settlement The security's settlement date.
     *                              The security's settlement date is the date after the issue date when the
     *                              security is traded to the buyer.
     * @param mixed $maturity The security's maturity date.
     *                                The maturity date is the date when the security expires.
     * @param mixed $issue The security's issue date
     * @param mixed $rate The security's interest rate at date of issue
     * @param mixed $yield The security's annual yield
     * @param mixed $basis The type of day count to use.
     *                         0 or omitted    US (NASD) 30/360
     *                         1               Actual/actual
     *                         2               Actual/360
     *                         3               Actual/365
     *                         4               European 30/360
     *
     * @return float|string Result, or a string containing an error
     */
    public static function priceAtMaturity(mixed $settlement, mixed $maturity, mixed $issue, mixed $rate, mixed $yield, mixed $basis = FinancialConstants::BASIS_DAYS_PER_YEAR_NASD)
    {
        $settlement = Functions::flattenSingleValue($settlement);
        $maturity = Functions::flattenSingleValue($maturity);
        $issue = Functions::flattenSingleValue($issue);
        $rate = Functions::flattenSingleValue($rate);
        $yield = Functions::flattenSingleValue($yield);
        $basis = $basis === null ? FinancialConstants::BASIS_DAYS_PER_YEAR_NASD : Functions::flattenSingleValue($basis);
        try {
            $settlement = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateSettlementDate($settlement);
            $maturity = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateMaturityDate($maturity);
            \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateSecurityPeriod($settlement, $maturity);
            $issue = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateIssueDate($issue);
            $rate = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateRate($rate);
            $yield = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateYield($yield);
            $basis = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateBasis($basis);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        $daysPerYear = Helpers::daysPerYear(Functions::scalar(DateTimeExcel\DateParts::year($settlement)), $basis);
        if (!\is_numeric($daysPerYear)) {
            return $daysPerYear;
        }
        $daysBetweenIssueAndSettlement = Functions::scalar(DateTimeExcel\YearFrac::fraction($issue, $settlement, $basis));
        if (!\is_numeric($daysBetweenIssueAndSettlement)) {
            //    return date error
            return StringHelper::convertToString($daysBetweenIssueAndSettlement);
        }
        $daysBetweenIssueAndSettlement *= $daysPerYear;
        $daysBetweenIssueAndMaturity = Functions::scalar(DateTimeExcel\YearFrac::fraction($issue, $maturity, $basis));
        if (!\is_numeric($daysBetweenIssueAndMaturity)) {
            //    return date error
            return StringHelper::convertToString($daysBetweenIssueAndMaturity);
        }
        $daysBetweenIssueAndMaturity *= $daysPerYear;
        $daysBetweenSettlementAndMaturity = Functions::scalar(DateTimeExcel\YearFrac::fraction($settlement, $maturity, $basis));
        if (!\is_numeric($daysBetweenSettlementAndMaturity)) {
            //    return date error
            return StringHelper::convertToString($daysBetweenSettlementAndMaturity);
        }
        $daysBetweenSettlementAndMaturity *= $daysPerYear;
        return (100 + $daysBetweenIssueAndMaturity / $daysPerYear * $rate * 100) / (1 + $daysBetweenSettlementAndMaturity / $daysPerYear * $yield) - $daysBetweenIssueAndSettlement / $daysPerYear * $rate * 100;
    }
    /**
     * RECEIVED.
     *
     * Returns the amount received at maturity for a fully invested Security.
     *
     * @param mixed $settlement The security's settlement date.
     *                              The security settlement date is the date after the issue date when the security
     *                                  is traded to the buyer.
     * @param mixed $maturity The security's maturity date.
     *                            The maturity date is the date when the security expires.
     * @param mixed $investment The amount invested in the security
     * @param mixed $discount The security's discount rate
     * @param mixed $basis The type of day count to use.
     *                         0 or omitted    US (NASD) 30/360
     *                         1               Actual/actual
     *                         2               Actual/360
     *                         3               Actual/365
     *                         4               European 30/360
     *
     * @return float|string Result, or a string containing an error
     */
    public static function received(mixed $settlement, mixed $maturity, mixed $investment, mixed $discount, mixed $basis = FinancialConstants::BASIS_DAYS_PER_YEAR_NASD)
    {
        $settlement = Functions::flattenSingleValue($settlement);
        $maturity = Functions::flattenSingleValue($maturity);
        $investment = Functions::flattenSingleValue($investment);
        $discount = Functions::flattenSingleValue($discount);
        $basis = $basis === null ? FinancialConstants::BASIS_DAYS_PER_YEAR_NASD : Functions::flattenSingleValue($basis);
        try {
            $settlement = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateSettlementDate($settlement);
            $maturity = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateMaturityDate($maturity);
            \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateSecurityPeriod($settlement, $maturity);
            $investment = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateFloat($investment);
            $discount = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateDiscount($discount);
            $basis = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateBasis($basis);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($investment <= 0) {
            return ExcelError::NAN();
        }
        $daysBetweenSettlementAndMaturity = DateTimeExcel\YearFrac::fraction($settlement, $maturity, $basis);
        if (!\is_numeric($daysBetweenSettlementAndMaturity)) {
            //    return date error
            return StringHelper::convertToString(Functions::scalar($daysBetweenSettlementAndMaturity));
        }
        return $investment / (1 - $discount * $daysBetweenSettlementAndMaturity);
    }
}
