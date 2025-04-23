<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\ArrayEnabled;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Combinations;
class HyperGeometric
{
    use ArrayEnabled;
    /**
     * HYPGEOMDIST.
     *
     * Returns the hypergeometric distribution. HYPGEOMDIST returns the probability of a given number of
     * sample successes, given the sample size, population successes, and population size.
     *
     * @param mixed $sampleSuccesses Integer number of successes in the sample
     *                      Or can be an array of values
     * @param mixed $sampleNumber Integer size of the sample
     *                      Or can be an array of values
     * @param mixed $populationSuccesses Integer number of successes in the population
     *                      Or can be an array of values
     * @param mixed $populationNumber Integer population size
     *                      Or can be an array of values
     *
     * @return array|float|string If an array of numbers is passed as an argument, then the returned result will also be an array
     *            with the same dimensions
     */
    public static function distribution(mixed $sampleSuccesses, mixed $sampleNumber, mixed $populationSuccesses, mixed $populationNumber) : array|string|float
    {
        if (\is_array($sampleSuccesses) || \is_array($sampleNumber) || \is_array($populationSuccesses) || \is_array($populationNumber)) {
            return self::evaluateArrayArguments([self::class, __FUNCTION__], $sampleSuccesses, $sampleNumber, $populationSuccesses, $populationNumber);
        }
        try {
            $sampleSuccesses = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\DistributionValidations::validateInt($sampleSuccesses);
            $sampleNumber = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\DistributionValidations::validateInt($sampleNumber);
            $populationSuccesses = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\DistributionValidations::validateInt($populationSuccesses);
            $populationNumber = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\DistributionValidations::validateInt($populationNumber);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        if ($sampleSuccesses < 0 || $sampleSuccesses > $sampleNumber || $sampleSuccesses > $populationSuccesses) {
            return ExcelError::NAN();
        }
        if ($sampleNumber <= 0 || $sampleNumber > $populationNumber) {
            return ExcelError::NAN();
        }
        if ($populationSuccesses <= 0 || $populationSuccesses > $populationNumber) {
            return ExcelError::NAN();
        }
        $successesPopulationAndSample = (float) Combinations::withoutRepetition($populationSuccesses, $sampleSuccesses);
        $numbersPopulationAndSample = (float) Combinations::withoutRepetition($populationNumber, $sampleNumber);
        $adjustedPopulationAndSample = (float) Combinations::withoutRepetition($populationNumber - $populationSuccesses, $sampleNumber - $sampleSuccesses);
        return $successesPopulationAndSample * $adjustedPopulationAndSample / $numbersPopulationAndSample;
    }
}
