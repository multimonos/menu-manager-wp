<?php

namespace MenuManager\Types\Export;

class ExportConfig {
    // CONFIG
    public ExportContext $context = ExportContext::Cli;
    public ExportFormat $format = ExportFormat::Csv;

    /* @var string|null Output path */
    public ?string $target = null;

    // MENUS is not considered a filter.
    /* @var int|string[] Menus to include in the export. */
    public array $menus = [];

    // FILTERS
    /* @var int|string[] Filter by item_id */
    public array $itemFilter = []; // @todo filter by item id

    /* @var int|string[] Filter by item uuid */
    public array $uuidFilter = []; // @todo filter by uuid

    /* @var int|string[] Filter by image_id */
    public array $imageIdFilter = []; // @todo filter by image id

    /* @var string[] Filter by item tag */
    public array $tagFilter = []; // @todo filter by tag

    /* @var string[] Filter by partial match on item type */
    public array $typeFilter = []; // @todo filter by partial match on item types

    /* @var string[] Filter items by partial match on item title */
    public array $titleFilter = []; // @todo filter by partial match on item title

    public function hasFilters(): bool {
        return ! empty( $this->itemFilter )
            || ! empty( $this->uuidFilter )
            || ! empty( $this->imageIdFilter )
            || ! empty( $this->tagFilter )
            || ! empty( $this->typeFilter )
            || ! empty( $this->titleFilter );
    }
}