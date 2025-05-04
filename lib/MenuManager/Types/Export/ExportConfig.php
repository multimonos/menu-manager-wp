<?php

namespace MenuManager\Types\Export;

class ExportConfig {
    // CONFIG
    public ExportContext $context = ExportContext::Cli;
    public ExportFormat $format = ExportFormat::Csv;

    /* @var string|null Output path */
    public ?string $target = null;

    // FILTERS
    /* @var int|string[] Filter by list of menu ids ( or slugs ) */
    public array $menuFilter = [];

    /* @var int|string[] Filter items by item_id */
    public array $itemFilter = [];

    /* @var int|string[] Filter items by item uuid */
    public array $uuidFilter = [];

    /* @var int|string[] Filter items by image_id */
    public array $imageIdFilter = [];

    /* @var string[] Filter items by partial item type match */
    public array $tagFilter = [];

    /* @var string[] Filter items by partial item type match */
    public array $typeFilter = [];

    /* @var string[] Partial match on item title */
    public array $titleFilter = [];

    public function hasFilters(): bool {
        return ! empty( $this->menuFilter )
            || ! empty( $this->itemFilter )
            || ! empty( $this->uuidFilter )
            || ! empty( $this->imageIdFilter )
            || ! empty( $this->tagFilter )
            || ! empty( $this->typeFilter )
            || ! empty( $this->titleFilter );
    }
}