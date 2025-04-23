<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer;

class BstoreContainer
{
    /**
     * BLIP Store Entries. Each of them holds one BLIP (Big Large Image or Picture).
     *
     * @var BstoreContainer\BSE[]
     */
    private array $BSECollection = [];
    /**
     * Add a BLIP Store Entry.
     */
    public function addBSE(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer\BstoreContainer\BSE $BSE) : void
    {
        $this->BSECollection[] = $BSE;
        $BSE->setParent($this);
    }
    /**
     * Get the collection of BLIP Store Entries.
     *
     * @return BstoreContainer\BSE[]
     */
    public function getBSECollection() : array
    {
        return $this->BSECollection;
    }
}
