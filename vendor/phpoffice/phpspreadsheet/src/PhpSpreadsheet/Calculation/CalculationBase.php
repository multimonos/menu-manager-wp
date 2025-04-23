<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation;

class CalculationBase
{
    /**
     * Get a list of all implemented functions as an array of function objects.
     *
     * return array<string, array<string, mixed>>
     */
    public static function getFunctions() : array
    {
        return \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\FunctionArray::$phpSpreadsheetFunctions;
    }
    /**
     * Get address of list of all implemented functions as an array of function objects.
     *
     * @return array<string, array<string, mixed>>
     */
    protected static function &getFunctionsAddress() : array
    {
        return \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\FunctionArray::$phpSpreadsheetFunctions;
    }
    /**
     * @param array<string, array<string, mixed>> $value
     */
    public static function addFunction(string $key, array $value) : bool
    {
        $key = \strtoupper($key);
        if (\array_key_exists($key, \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\FunctionArray::$phpSpreadsheetFunctions)) {
            return \false;
        }
        $value['custom'] = \true;
        \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\FunctionArray::$phpSpreadsheetFunctions[$key] = $value;
        return \true;
    }
    public static function removeFunction(string $key) : bool
    {
        $key = \strtoupper($key);
        if (\array_key_exists($key, \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\FunctionArray::$phpSpreadsheetFunctions)) {
            if (\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\FunctionArray::$phpSpreadsheetFunctions[$key]['custom'] ?? \false) {
                unset(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\FunctionArray::$phpSpreadsheetFunctions[$key]);
                return \true;
            }
        }
        return \false;
    }
}
