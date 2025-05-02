<?php

namespace MenuManager\Tasks\Impex;

use MenuManager\Model\Impex;
use MenuManager\Model\Job;
use MenuManager\Service\Database;
use MenuManager\Service\Logger;
use MenuManager\Tasks\Exception;
use MenuManager\Tasks\TaskResult;
use MenuManager\Vendor\League\Csv\Reader;

class LoadTask {

    public function run( string $path ): TaskResult {

        $conn = Database::load()->getConnection();

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
                'source' => $path,
//                'type'   => 'import', // @todo create these meta props
//                'status' => 'created', // @todo create these meta props
//                'source' => $path, // @todo create these meta props
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
                    'uuid'           => empty( $record['uuid'] ) ? null : $record['uuid'],
                    'parent_id'      => empty( $record['parent_id'] ) ? null : $record['parent_id'],
                    'sort_order'     => empty( $record['sort_order'] ) ? null : $record['sort_order'],
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

            return TaskResult::success( "Loaded: {$path}", ['job' => $job] );

        } catch (Exception $e) {
            $conn->rollBack();
            return TaskResult::failure( "Load failed: " . $e->getMessage() );
        }
    }

}