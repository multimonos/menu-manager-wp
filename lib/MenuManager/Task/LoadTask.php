<?php

namespace MenuManager\Task;

use MenuManager\Database\db;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Job;
use MenuManager\Logger;
use MenuManager\Vendor\League\Csv\Reader;

class LoadTask {

    public function run( string $path ): TaskResult {

        $conn = db::load()->getConnection();

        $conn->beginTransaction();

        try {
            // database
            global $wpdb;

            // csv : reader
            $reader = Reader::createFromPath( $path, 'r' );
            $import_id = uniqid();

            // csv : header rows
            $reader->setHeaderOffset( 0 );
            $headers = $reader->getHeader();

            if ( ! in_array( 'action', $headers ) ) {
                throw new \Exception( "Header row is missing" );
            }

            // job
            $job = Job::create( [
                'type'   => 'import',
                'status' => 'created',
                'source' => $path,
            ] );

            Logger::taskInfo( 'load', 'src=' . $path );


            // impex : load rows
            $records = $reader->getRecords();

            foreach ( $records as $record ) {

                // skip any header row
                if ( strtolower( $record['action'] ) == 'action' || strtolower( $record['menu'] ) == 'menu' ) {
                    continue;
                }

                // impex insert
                Impex::create( [
                    'job_id'         => $job->id,
                    'action'         => $record['action'],
                    'uuid'           => $record['uuid'],
                    'description'    => $record['description'],
                    'image_ids'      => $record['image_ids'],
                    'is_glutensmart' => Impex::toBoolean( $record['is_glutensmart'] ),
                    'is_new'         => Impex::toBoolean( $record['is_new'] ),
                    'is_organic'     => Impex::toBoolean( $record['is_organic'] ),
                    'is_vegan'       => Impex::toBoolean( $record['is_vegan'] ),
                    'is_vegetarian'  => Impex::toBoolean( $record['is_vegetarian'] ),
                    'item_id'        => (int)$record['item_id'],
                    'menu'           => $record['menu'], // id or slug
                    'page'           => $record['page'],
                    'prices'         => $record['prices'],
                    'title'          => $record['title'],
                    'type'           => $record['type'],
                ] );
            }

//            print_r( ['record' => $record] );

            $conn->commit();

            return TaskResult::success( "Loaded: {$path}" );

        } catch (Exception $e) {
            $conn->rollBack();
            return TaskResult::failure( "Load failed: " . $e->getMessage() );
        }
    }

}