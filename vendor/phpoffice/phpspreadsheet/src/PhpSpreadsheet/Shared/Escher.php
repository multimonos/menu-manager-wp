<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared;

class Escher
{
    /**
     * Drawing Group Container.
     */
    private ?\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer $dggContainer = null;
    /**
     * Drawing Container.
     */
    private ?\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DgContainer $dgContainer = null;
    /**
     * Get Drawing Group Container.
     */
    public function getDggContainer() : ?\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer
    {
        return $this->dggContainer;
    }
    /**
     * Set Drawing Group Container.
     */
    public function setDggContainer(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer $dggContainer) : \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer
    {
        return $this->dggContainer = $dggContainer;
    }
    /**
     * Get Drawing Container.
     */
    public function getDgContainer() : ?\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DgContainer
    {
        return $this->dgContainer;
    }
    /**
     * Set Drawing Container.
     */
    public function setDgContainer(\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DgContainer $dgContainer) : \MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Shared\Escher\DgContainer
    {
        return $this->dgContainer = $dgContainer;
    }
}
