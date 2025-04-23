<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting;

class ConditionalDataBar
{
    private ?bool $showValue = null;
    private ?\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormatValueObject $minimumConditionalFormatValueObject = null;
    private ?\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormatValueObject $maximumConditionalFormatValueObject = null;
    private string $color = '';
    private ?\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormattingRuleExtension $conditionalFormattingRuleExt = null;
    public function getShowValue() : ?bool
    {
        return $this->showValue;
    }
    public function setShowValue(bool $showValue) : self
    {
        $this->showValue = $showValue;
        return $this;
    }
    public function getMinimumConditionalFormatValueObject() : ?\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormatValueObject
    {
        return $this->minimumConditionalFormatValueObject;
    }
    public function setMinimumConditionalFormatValueObject(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormatValueObject $minimumConditionalFormatValueObject) : self
    {
        $this->minimumConditionalFormatValueObject = $minimumConditionalFormatValueObject;
        return $this;
    }
    public function getMaximumConditionalFormatValueObject() : ?\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormatValueObject
    {
        return $this->maximumConditionalFormatValueObject;
    }
    public function setMaximumConditionalFormatValueObject(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormatValueObject $maximumConditionalFormatValueObject) : self
    {
        $this->maximumConditionalFormatValueObject = $maximumConditionalFormatValueObject;
        return $this;
    }
    public function getColor() : string
    {
        return $this->color;
    }
    public function setColor(string $color) : self
    {
        $this->color = $color;
        return $this;
    }
    public function getConditionalFormattingRuleExt() : ?\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormattingRuleExtension
    {
        return $this->conditionalFormattingRuleExt;
    }
    public function setConditionalFormattingRuleExt(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormattingRuleExtension $conditionalFormattingRuleExt) : self
    {
        $this->conditionalFormattingRuleExt = $conditionalFormattingRuleExt;
        return $this;
    }
}
