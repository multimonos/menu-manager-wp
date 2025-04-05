<?php

namespace MenuManager\Wpcli\Commands;

use League\Csv\Writer;
use MenuManager\Database\PostType\MenuPost;
use WP_CLI;

class RootCommands {
    /**
     * Export menu to CSV.
     *
     * ## OPTIONS
     *
     * <menu_id>
     * : ID of the menu.
     *
     * [<csv_file>]
     * : The CSV file to write.
     *
     * ## EXAMPLES
     *
     *    wp mm export 666 export.csv
     *
     * @when after_wp_load
     */
    public function export( $args, $assoc_args ) {

        // menu
        $menu_id = $args[0];

        $menu = MenuPost::find( $menu_id );


        if ( ! $menu instanceof \WP_Post ) {
            WP_CLI::error( "Menu not found" );
            return;
        }

        // filepath
        $dst = $args[1] ?? null;
        $dst = empty( $dst )
            ? $this->filenameFromPost( $menu )
            : sanitize_file_name( $dst );


        // write
        $writer = Writer::createFromPath( $dst, 'w+' );
        $writer->insertOne( ['name', 'email'] );
        $writer->insertAll( [
            ['Alice', 'alice@example.com'],
            ['Bob', 'bob@example.com'],
        ] );
    }


    protected function filenameFromPost( \WP_Post $post ) {
        $datetime = date( 'Ymd\THi' );
        return "export-{$post->post_type}_{$post->post_name}_{$post->ID}_{$datetime}.csv";
    }
}