<?php

namespace MenuManager\Wpcli\Commands;

use MenuManager\Tasks\Impex\LoadTask;
use MenuManager\Tasks\Menu\ExportTask;
use MenuManager\Types\Export\ExportConfig;
use MenuManager\Types\Export\ExportContext;
use MenuManager\Types\Export\ExportFormat;
use MenuManager\Utils\Splitter;
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
     * : Comma separated list of Menu Ids or Slugs to include. NOTE: Quote string if list has spaces. Use "*" for all menus.
     *
     * [<file>]
     * : The CSV file to write.
     *
     * [--format=<format>]
     * : Output format. Options: csv, excel. Default: csv.
     *
     * [--page=<page>]
     * : Comma separated of pages.
     *
     * [--uuid=<item_uuids>]
     * : Comma separated of item UUIDs to match.
     *
     * [--parent=<ids>]
     * : Comma separated of item parent ids to match.
     *
     * [--id=<ids>]
     * : Comma separated of item ids to match.
     *
     * [--type=<type>]
     * : Command separated match on item type.
     *
     * [--title=<partial_match_string>]
     * : Comma separated list of partial match on item title.
     *
     * [--image=<image_ids>]
     * : Command separated list of Image Ids to match.
     *
     * @when after_wp_load
     */
    public function export( $args, $assoc_args ) {

        $config = new ExportConfig();
        // Export config.
        $config->context = ExportContext::Cli;
        $config->format = ExportFormat::from( $assoc_args['format'] ?? ExportFormat::Csv->value );
        $config->target = $args[1] ?? null;
        $config->menus = Splitter::unique( $args[0] ?? '' );

        // Result Filters
        $filters = [
            'page'   => 'page',
            'uuid'   => 'uuid',
            'parent' => 'parent_id',
            'type'   => 'type',
            'id'     => 'item_id',
            'image'  => 'image_ids',
            'title'  => 'title',
        ];

        foreach ( $filters as $k => $field ) {
            $value = Splitter::unique( $assoc_args[$k] ?? '' );
            if ( ! empty( $value ) ) {
                $config->filterBy( $field, $value );
            }
        }

        // Task
        $task = new ExportTask();
        $rs = $task->run( $config );

        // Result
        CommandHelper::sendTaskResult( $rs );
    }


}