<?php

namespace MenuManager\Types\Export;

class ExportConfig {
    const ALL_MENUS = 'all';

    // CONFIG
    public ExportContext $context = ExportContext::Cli;
    public ExportFormat $format = ExportFormat::Csv;

    /* @var string|null Output path */
    public ?string $target = null;

    // MENUS is not considered a filter.
    /* @var int|string[] Menus to include in the export. */
    public array $menus = [];

    // FILTERS
    /* @var ExportFilter[] Filter by impex csv fields */
    public array $filters = [];

    public function filterBy( string $key, array $values ): void {
        switch ( $key ) {
            case 'page':
            case 'uuid':
            case 'parent_id':
            case 'item_id':
            case 'type':
                $this->filters[] = ExportFilter::make( 'in_array', $key, $values );
                break;

            case 'image_ids':
                $this->filters[] = ExportFilter::make( 'csv_in_array', $key, $values );
                break;

            case 'title':
                $this->filters[] = ExportFilter::make( 'contains', $key, $values );
                break;
        }
    }

    public function hasFilters(): bool {
        return ! empty( $this->filters );
    }
}