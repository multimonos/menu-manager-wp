<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\StatisticalValidations;
class DistributionValidations extends StatisticalValidations
{
    public static function validateProbability(mixed $probability) : float
    {
        $probability = self::validateFloat($probability);
        if ($probability < 0.0 || $probability > 1.0) {
            throw new Exception(ExcelError::NAN());
        }
        return $probability;
    }
}
