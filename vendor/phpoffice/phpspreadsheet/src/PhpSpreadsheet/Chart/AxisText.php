<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Chart;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\Font;
class AxisText extends \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Chart\Properties
{
    private ?int $rotation = null;
    private Font $font;
    public function __construct()
    {
        parent::__construct();
        $this->font = new Font();
        $this->font->setSize(null, \true);
    }
    public function setRotation(?int $rotation) : self
    {
        $this->rotation = $rotation;
        return $this;
    }
    public function getRotation() : ?int
    {
        return $this->rotation;
    }
    public function getFillColorObject() : \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Chart\ChartColor
    {
        $fillColor = $this->font->getChartColor();
        if ($fillColor === null) {
            $fillColor = new \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Chart\ChartColor();
            $this->font->setChartColorFromObject($fillColor);
        }
        return $fillColor;
    }
    public function getFont() : Font
    {
        return $this->font;
    }
    public function setFont(Font $font) : self
    {
        $this->font = $font;
        return $this;
    }
    /**
     * Implement PHP __clone to create a deep clone, not just a shallow copy.
     */
    public function __clone()
    {
        parent::__clone();
        $this->font = clone $this->font;
    }
}
