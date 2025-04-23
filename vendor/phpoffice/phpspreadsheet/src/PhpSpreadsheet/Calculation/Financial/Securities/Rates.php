<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Constants as FinancialConstants;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Functions;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\StringHelper;
class Rates
{
    /**
     * DISC.
     *
     * Returns the discount rate for a security.
     *
     * Excel Function:
     *        DISC(settlement,maturity,price,redemption[,basis])
     *
     * @param mixed $settlement The security's settlement date.
     *                              The security settlement date is the date after the issue
     *                                  date when the security is traded to the buyer.
     * @param mixed $maturity The security's maturity date.
     *                            The maturity date is the date when the security expires.
     * @param mixed $price The security's price per $100 face value
     * @param mixed $redemption The security's redemption value per $100 face value
     * @param mixed $basis The type of day count to use.
     *                         0 or omitted    US (NASD) 30/360
     *                         1               Actual/actual
     *                         2               Actual/360
     *                         3               Actual/365
     *                         4               European 30/360
     */
    public static function discount(mixed $settlement, mixed $maturity, mixed $price, mixed $redemption, mixed $basis = FinancialConstants::BASIS_DAYS_PER_YEAR_NASD) : float|string
    {
        $settlement = Functions::flattenSingleValue($settlement);
        $maturity = Functions::flattenSingleValue($maturity);
        $price = Functions::flattenSingleValue($price);
        $redemption = Functions::flattenSingleValue($redemption);
        $basis = $basis === null ? FinancialConstants::BASIS_DAYS_PER_YEAR_NASD : Functions::flattenSingleValue($basis);
        try {
            $settlement = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateSettlementDate($settlement);
            $maturity = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateMaturityDate($maturity);
            \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateSecurityPeriod($settlement, $maturity);
            $price = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validatePrice($price);
            $redemption = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateRedemption($redemption);
            $basis = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateBasis($basis);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($price <= 0.0) {
            return ExcelError::NAN();
        }
        $daysBetweenSettlementAndMaturity = Functions::scalar(DateTimeExcel\YearFrac::fraction($settlement, $maturity, $basis));
        if (!\is_numeric($daysBetweenSettlementAndMaturity)) {
            //    return date error
            return StringHelper::convertToString($daysBetweenSettlementAndMaturity);
        }
        return (1 - $price / $redemption) / $daysBetweenSettlementAndMaturity;
    }
    /**
     * INTRATE.
     *
     * Returns the interest rate for a fully invested security.
     *
     * Excel Function:
     *        INTRATE(settlement,maturity,investment,redemption[,basis])
     *
     * @param mixed $settlement The security's settlement date.
     *                              The security settlement date is the date after the issue date when the security
     *                                  is traded to the buyer.
     * @param mixed $maturity The security's maturity date.
     *                            The maturity date is the date when the security expires.
     * @param mixed $investment the amount invested in the security
     * @param mixed $redemption the amount to be received at maturity
     * @param mixed $basis The type of day count to use.
     *                         0 or omitted    US (NASD) 30/360
     *                         1               Actual/actual
     *                         2               Actual/360
     *                         3               Actual/365
     *                         4               European 30/360
     */
    public static function interest(mixed $settlement, mixed $maturity, mixed $investment, mixed $redemption, mixed $basis = FinancialConstants::BASIS_DAYS_PER_YEAR_NASD) : float|string
    {
        $settlement = Functions::flattenSingleValue($settlement);
        $maturity = Functions::flattenSingleValue($maturity);
        $investment = Functions::flattenSingleValue($investment);
        $redemption = Functions::flattenSingleValue($redemption);
        $basis = $basis === null ? FinancialConstants::BASIS_DAYS_PER_YEAR_NASD : Functions::flattenSingleValue($basis);
        try {
            $settlement = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateSettlementDate($settlement);
            $maturity = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateMaturityDate($maturity);
            \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateSecurityPeriod($settlement, $maturity);
            $investment = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateFloat($investment);
            $redemption = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateRedemption($redemption);
            $basis = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\SecurityValidations::validateBasis($basis);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($investment <= 0) {
            return ExcelError::NAN();
        }
        $daysBetweenSettlementAndMaturity = Functions::scalar(DateTimeExcel\YearFrac::fraction($settlement, $maturity, $basis));
        if (!\is_numeric($daysBetweenSettlementAndMaturity)) {
            //    return date error
            return StringHelper::convertToString($daysBetweenSettlementAndMaturity);
        }
        return ($redemption / $investment - 1) / $daysBetweenSettlementAndMaturity;
    }
}
