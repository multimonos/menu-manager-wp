<?php

namespace MenuManager\Wpcli\Commands;

use League\Csv\Writer;
use MenuManager\Database\Model\Location;


class ExportCommand {

    public function export( $location_id, $dst ): CommandResult {
        // get location
        $location = Location::get( $location_id );

        if ( ! $location instanceof \WP_Post ) {
            return CommandResult::failure( "Location not found" );
        }

        // get or create filename
        if ( empty( $dst ) ) {
            $dst = $this->filenameFromLocation( $location );
        }

        // get the menu data

        // map onto MenuCsv model

        // write the csv


        // Writing CSV
        $writer = Writer::createFromPath( $dst, 'w+' );
        $writer->insertOne( ['name', 'email'] );
        $writer->insertAll( [
            ['Alice', 'alice@example.com'],
            ['Bob', 'bob@example.com'],
        ] );

        return CommandResult::success( "Menu export: {$dst}" );
    }

    protected function filenameFromLocation( \WP_Post $location ) {
        $datetime = date( 'Ymd\THi' );
        return "export-menu_{$location->post_name}_{$location->ID}_{$datetime}.csv";
    }
}