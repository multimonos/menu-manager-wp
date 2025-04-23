<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer\BstoreContainer;

use MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer\BstoreContainer;
class BSE
{
    const BLIPTYPE_ERROR = 0x0;
    const BLIPTYPE_UNKNOWN = 0x1;
    const BLIPTYPE_EMF = 0x2;
    const BLIPTYPE_WMF = 0x3;
    const BLIPTYPE_PICT = 0x4;
    const BLIPTYPE_JPEG = 0x5;
    const BLIPTYPE_PNG = 0x6;
    const BLIPTYPE_DIB = 0x7;
    const BLIPTYPE_TIFF = 0x11;
    const BLIPTYPE_CMYKJPEG = 0x12;
    /**
     * The parent BLIP Store Entry Container.
     * Property is currently unused.
     */
    private BstoreContainer $parent;
    /**
     * The BLIP (Big Large Image or Picture).
     */
    private ?\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer\BstoreContainer\BSE\Blip $blip = null;
    /**
     * The BLIP type.
     */
    private int $blipType;
    /**
     * Set parent BLIP Store Entry Container.
     */
    public function setParent(BstoreContainer $parent) : void
    {
        $this->parent = $parent;
    }
    public function getParent() : BstoreContainer
    {
        return $this->parent;
    }
    /**
     * Get the BLIP.
     */
    public function getBlip() : ?\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer\BstoreContainer\BSE\Blip
    {
        return $this->blip;
    }
    /**
     * Set the BLIP.
     */
    public function setBlip(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer\BstoreContainer\BSE\Blip $blip) : void
    {
        $this->blip = $blip;
        $blip->setParent($this);
    }
    /**
     * Get the BLIP type.
     */
    public function getBlipType() : int
    {
        return $this->blipType;
    }
    /**
     * Set the BLIP type.
     */
    public function setBlipType(int $blipType) : void
    {
        $this->blipType = $blipType;
    }
}
