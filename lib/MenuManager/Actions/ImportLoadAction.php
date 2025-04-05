<?php

namespace MenuManager\Actions;

use League\Csv\Reader;
use MenuManager\Database\DB;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Job;
use SimplePie\Exception;

class ImportLoadAction {
    public static function bool( mixed $v ): int {
        return in_array( $v, ['yes', 'true', true, 'Y'] ) ? 1 : 0;
    }

    public function run( string $path ) {

        DB::startTransaction();

        try {
            // database
            global $wpdb;

            // csv : reader
            $reader = Reader::createFromPath( $path, 'r' );
            $import_id = uniqid();

            // csv : header rows
            $reader->setHeaderOffset( 0 );
            $headers = $reader->getHeader();

            // csv : debug
            print_r( [
                'headers' => $headers,
                'import'  => $import_id,
            ] );

            // job create
            $rs0 = Job::createImport();

            // guard : job create err
            if ( $rs0 === false ) {
                throw new Exception( $wpdb->last_error );
            }

            // job create ok
            $job_id = $wpdb->insert_id;

            // impex : load rows
            $records = $reader->getRecords();

            foreach ( $records as $record ) {

                // skip any header row
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

                // guard : impex insert
                if ( $rs === false ) {
                    throw new Exception( $wpdb->last_error );
                }
            }

            print_r( ['record' => $record] );

            DB::commit();

            return ActionResult::success( "Imported: {$path}" );

        } catch (Exception $e) {
            DB::rollback();
            return ActionResult::failure( "Import failed: " . $e->getMessage() );
        }
    }

}