<?php

namespace MenuManager\Wpcli\Commands;

use MenuManager\Tasks\Impex\LoadTask;
use MenuManager\Tasks\Menu\ExportTask;
use MenuManager\Types\Export\ExportConfig;
use MenuManager\Types\Export\ExportContext;
use MenuManager\Types\Export\ExportFormat;
use MenuManager\Wpcli\CliHelper;
use MenuManager\Wpcli\Util\CommandHelper;
use WP_CLI;

class RootCommands {

    /**
     * Load a CSV to create an import job.
     *
     * ## OPTIONS
     *
     * <file>
     * : The CSV file to consume.
     *
     * ## EXAMPLES
     *
     *      wp mm import load impex-foobar.csv
     *
     * @when after_wp_load
     */
    public function load( $args, $assoc_args ) {
        list( $path ) = $args;

        // guard : file
        if ( ! file_exists( $path ) ) {
            WP_CLI::error( "File not found $path" );
        }

        // load
        $task = new LoadTask();
        $rs = $task->run( $path );

        CommandHelper::sendTaskResult( $rs );
    }

    /**
     * Export menu to CSV or Excel.
     *
     * ## OPTIONS
     *
     * <menu_id>
     * : Command separated list of Menu Ids or Slugs to include. NOTE: Quote string if list has spaces.
     *
     * [<file>]
     * : The CSV file to write.
     *
     * [--format=<format>]
     * : Output format. Options: csv, excel. Default: csv.
     *
     * [--item-id=<item_ids>]
     * : Comma separated of item ids to match.
     *
     * [--item-uuid=<item_uuids>]
     * : Comma separated of item UUIDs to match.
     *
     * [--item-type=<partial_match_string>]
     * : Command separated list of partial match on item type.
     *
     * [--item-title=<partial_match_string>]
     * : Comma separated list of partial match on item title.
     *
     * [--item-image=<image_ids>]
     * : Command separated list of Image Ids to match.
     *
     * [--item-tag=<tag_ids>]
     * : Command separated list of tags to match.
     *
     * @when after_wp_load
     */
    public function export( $args, $assoc_args ) {

        $config = new ExportConfig();
        // Export config.
        $config->context = ExportContext::Cli;
        $config->format = ExportFormat::from( $assoc_args['format'] ?? ExportFormat::Csv->value );
        $config->target = $args[1] ?? null;

        // Result Filters
        $config->menuFilter = CliHelper::split( $args[0] ?? '' );
        $config->itemFilter = CliHelper::split( $assoc_args['item-id'] ?? null );
        $config->uuidFilter = CliHelper::split( $assoc_args['item-uuid'] ?? null );
        $config->imageIdFilter = CliHelper::split( $assoc_args['item-image'] ?? null );
        $config->tagFilter = CliHelper::split( $assoc_args['item-tag'] ?? null );
        $config->typeFilter = CliHelper::split( $assoc_args['item-type'] ?? null );
        $config->titleFilter = CliHelper::split( $assoc_args['item-title'] ?? null );

        // Task
        $task = new ExportTask();
        $rs = $task->run( $config );

        // Result
        CommandHelper::sendTaskResult( $rs );
    }


}