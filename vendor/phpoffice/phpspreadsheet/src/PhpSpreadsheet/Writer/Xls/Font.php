<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Writer\Xls;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\StringHelper;
class Font
{
    /**
     * Color index.
     */
    private int $colorIndex;
    /**
     * Font.
     */
    private \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\Font $font;
    /**
     * Constructor.
     */
    public function __construct(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\Font $font)
    {
        $this->colorIndex = 0x7fff;
        $this->font = $font;
    }
    /**
     * Set the color index.
     */
    public function setColorIndex(int $colorIndex) : void
    {
        $this->colorIndex = $colorIndex;
    }
    private static int $notImplemented = 0;
    /**
     * Get font record data.
     */
    public function writeFont() : string
    {
        $font_outline = self::$notImplemented;
        $font_shadow = self::$notImplemented;
        $icv = $this->colorIndex;
        // Index to color palette
        if ($this->font->getSuperscript()) {
            $sss = 1;
        } elseif ($this->font->getSubscript()) {
            $sss = 2;
        } else {
            $sss = 0;
        }
        $bFamily = 0;
        // Font family
        $bCharSet = \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Font::getCharsetFromFontName((string) $this->font->getName());
        // Character set
        $record = 0x31;
        // Record identifier
        $reserved = 0x0;
        // Reserved
        $grbit = 0x0;
        // Font attributes
        if ($this->font->getItalic()) {
            $grbit |= 0x2;
        }
        if ($this->font->getStrikethrough()) {
            $grbit |= 0x8;
        }
        if ($font_outline) {
            $grbit |= 0x10;
        }
        if ($font_shadow) {
            $grbit |= 0x20;
        }
        $data = \pack(
            'vvvvvCCCC',
            // Fontsize (in twips)
            $this->font->getSize() * 20,
            $grbit,
            // Colour
            $icv,
            // Font weight
            self::mapBold($this->font->getBold()),
            // Superscript/Subscript
            $sss,
            self::mapUnderline((string) $this->font->getUnderline()),
            $bFamily,
            $bCharSet,
            $reserved
        );
        $data .= StringHelper::UTF8toBIFF8UnicodeShort((string) $this->font->getName());
        $length = \strlen($data);
        $header = \pack('vv', $record, $length);
        return $header . $data;
    }
    /**
     * Map to BIFF5-BIFF8 codes for bold.
     */
    private static function mapBold(?bool $bold) : int
    {
        if ($bold === \true) {
            return 0x2bc;
            //  700 = Bold font weight
        }
        return 0x190;
        //  400 = Normal font weight
    }
    /**
     * Map of BIFF2-BIFF8 codes for underline styles.
     *
     * @var int[]
     */
    private static array $mapUnderline = [\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_NONE => 0x0, \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE => 0x1, \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_DOUBLE => 0x2, \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLEACCOUNTING => 0x21, \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_DOUBLEACCOUNTING => 0x22];
    /**
     * Map underline.
     */
    private static function mapUnderline(string $underline) : int
    {
        if (isset(self::$mapUnderline[$underline])) {
            return self::$mapUnderline[$underline];
        }
        return 0x0;
    }
}
