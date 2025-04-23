<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Reader\Xlsx;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use SimpleXMLElement;
class Hyperlinks
{
    private Worksheet $worksheet;
    private array $hyperlinks = [];
    public function __construct(Worksheet $workSheet)
    {
        $this->worksheet = $workSheet;
    }
    public function readHyperlinks(SimpleXMLElement $relsWorksheet) : void
    {
        foreach ($relsWorksheet->children(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Reader\Xlsx\Namespaces::RELATIONSHIPS)->Relationship as $elementx) {
            $element = Xlsx::getAttributes($elementx);
            if ($element->Type == \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Reader\Xlsx\Namespaces::HYPERLINK) {
                $this->hyperlinks[(string) $element->Id] = (string) $element->Target;
            }
        }
    }
    public function setHyperlinks(SimpleXMLElement $worksheetXml) : void
    {
        foreach ($worksheetXml->children(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Reader\Xlsx\Namespaces::MAIN)->hyperlink as $hyperlink) {
            $this->setHyperlink($hyperlink, $this->worksheet);
        }
    }
    private function setHyperlink(SimpleXMLElement $hyperlink, Worksheet $worksheet) : void
    {
        // Link url
        $linkRel = Xlsx::getAttributes($hyperlink, \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Reader\Xlsx\Namespaces::SCHEMA_OFFICE_DOCUMENT);
        $attributes = Xlsx::getAttributes($hyperlink);
        foreach (Coordinate::extractAllCellReferencesInRange($attributes->ref) as $cellReference) {
            $cell = $worksheet->getCell($cellReference);
            if (isset($linkRel['id'])) {
                $hyperlinkUrl = $this->hyperlinks[(string) $linkRel['id']] ?? null;
                if (isset($attributes['location'])) {
                    $hyperlinkUrl .= '#' . (string) $attributes['location'];
                }
                $cell->getHyperlink()->setUrl($hyperlinkUrl);
            } elseif (isset($attributes['location'])) {
                $cell->getHyperlink()->setUrl('sheet://' . (string) $attributes['location']);
            }
            // Tooltip
            if (isset($attributes['tooltip'])) {
                $cell->getHyperlink()->setTooltip((string) $attributes['tooltip']);
            }
        }
    }
}
