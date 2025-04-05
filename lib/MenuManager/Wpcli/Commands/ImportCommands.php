<?php

namespace MenuManager\Wpcli\Commands;

use League\Csv\Reader;
use MenuManager\Database\DB;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Job;


class ImportCommands {
    /**
     * Load a CSV to create an import job.
     *
     * ## OPTIONS
     *
     * <csv_file>
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
        global $wpdb;

        $reader = Reader::createFromPath( $path, 'r' );
        $import_id = uniqid();

        // header row
        $reader->setHeaderOffset( 0 );
        $headers = $reader->getHeader();

        // prepare
        print_r( [
            'headers' => $headers,
            'import'  => $import_id,
        ] );


        // db
        DB::startTransaction();

        try {

            // job create
            $rs0 = Job::createImport();

            // job create err
            if ( $rs0 === false ) {
                throw new Exception( $wpdb->last_error );
            }

            $job_id = $wpdb->insert_id;


            // impex rows
            $records = $reader->getRecords();

            foreach ( $records as $record ) {

                // skip any header rows to allow user to have as many as they want
                if ( strtolower( $record['action'] ) == 'action' || strtolower( $record['menu'] ) == 'menu' ) {
                    continue;
                }

                // impex insert
                $rs = $wpdb->insert( Impex::tablename(), [
                    'job_id'         => $job_id,
                    'action'         => $record['action'],
                    'menu'           => $record['menu'], // id or slug
                    'page'           => $record['page'],
                    'batch_id'       => $record['batch_id'],
                    'type'           => $record['type'],
                    'item_id'        => $record['item_id'],
                    'batch_id'       => $record['batch_id'],
                    'title'          => $record['title'],
                    'prices'         => $record['prices'],
                    'image_ids'      => $record['image_ids'],
                    'is_new'         => self::bool( $record['is_new'] ),
                    'is_glutensmart' => self::bool( $record['is_glutensmart'] ),
                    'is_organic'     => self::bool( $record['is_organic'] ),
                    'is_vegan'       => self::bool( $record['is_vegan'] ),
                    'is_vegetarian'  => self::bool( $record['is_vegetarian'] ),
                    'description'    => $record['description'],
                ] );

                // impex insert err
                if ( $rs === false ) {
                    throw new Exception( $wpdb->last_error );
                }
            }

            print_r( ['record' => $record] );

            DB::commit();
            return CommandResult::success( "Imported: {$path}" );

        } catch (Exception $e) {
            DB::rollback();
            return CommandResult::failure( "Import failed: " . $e->getMessage() );
        }
    }

    public static function bool( mixed $v ): int {
        return in_array( $v, ['yes', 'true', true, 'Y'] ) ? 1 : 0;
    }

}
