<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Xls;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\StringHelper;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\Wizard;
class ConditionalHelper
{
    /**
     * Formula parser.
     */
    protected \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Xls\Parser $parser;
    protected mixed $condition;
    protected string $cellRange;
    protected ?string $tokens = null;
    protected int $size;
    public function __construct(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Xls\Parser $parser)
    {
        $this->parser = $parser;
    }
    public function processCondition(mixed $condition, string $cellRange) : void
    {
        $this->condition = $condition;
        $this->cellRange = $cellRange;
        if (\is_int($condition) && $condition >= 0 && $condition <= 65535) {
            $this->size = 3;
            $this->tokens = \pack('Cv', 0x1e, $condition);
        } else {
            try {
                $formula = Wizard\WizardAbstract::reverseAdjustCellRef(StringHelper::convertToString($condition), $cellRange);
                $this->parser->parse($formula);
                $this->tokens = $this->parser->toReversePolish();
                $this->size = \strlen($this->tokens ?? '');
            } catch (PhpSpreadsheetException) {
                // In the event of a parser error with a formula value, we set the expression to ptgInt + 0
                $this->tokens = \pack('Cv', 0x1e, 0);
                $this->size = 3;
            }
        }
    }
    public function tokens() : ?string
    {
        return $this->tokens;
    }
    public function size() : int
    {
        return $this->size;
    }
}
