<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class NamedFormula extends \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\DefinedName
{
    /**
     * Create a new Named Formula.
     */
    public function __construct(string $name, ?Worksheet $worksheet = null, ?string $formula = null, bool $localOnly = \false, ?Worksheet $scope = null)
    {
        // Validate data
        if (!isset($formula)) {
            throw new \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Exception('You must specify a Formula value for a Named Formula');
        }
        parent::__construct($name, $worksheet, $formula, $localOnly, $scope);
    }
    /**
     * Get the formula value.
     */
    public function getFormula() : string
    {
        return $this->value;
    }
    /**
     * Set the formula value.
     */
    public function setFormula(string $formula) : self
    {
        if (!empty($formula)) {
            $this->value = $formula;
        }
        return $this;
    }
}
