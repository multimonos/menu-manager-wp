<?php

namespace MenuManager\Tasks\Impex;

use MenuManager\Model\Impex;
use MenuManager\Model\Job;
use MenuManager\Service\Database;
use MenuManager\Service\Filesystem;
use MenuManager\Service\Logger;
use MenuManager\Tasks\Exception;
use MenuManager\Tasks\TaskResult;
use MenuManager\Utils\UserHelper;
use MenuManager\Vendor\League\Csv\Reader;

class LoadTask {

    public function run( string $src ): TaskResult {

        $conn = Database::load()->getConnection();

        $conn->beginTransaction();

        try {
            // Guard file exists
            $fs = Filesystem::get();
            if ( ! $fs->exists( $src ) ) {
                return TaskResult::failure( "File not found {$src}" );
            }

            // Copy to the uploads folder...so can be downloaded later if needed.
            $filename = Filesystem::secureFilename( '.csv', 'job-' );
            $dst = Filesystem::pathFor( $filename );

            $rs = $fs->copy( $src, $dst );
            if ( is_wp_error( $rs ) ) {
                return TaskResult::failure( "Failed to copy {$src} to {$dst}." );
            }

            // csv : reader
            $reader = Reader::createFromPath( $src, 'r' );

            // csv : header rows
            $reader->setHeaderOffset( 0 );
            $headers = $reader->getHeader();

            if ( ! in_array( 'action', $headers ) ) {
                throw new \Exception( "Header row is missing" );
            }

            // job
            $job = Job::create( [
                'title'      => basename( $src ),
                'filename'   => $filename,
                'created_by' => UserHelper::currentUserEmail(),
            ] );

            Logger::taskInfo( 'load', 'src=' . $src );


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
                    'item_id'        => empty( $record['item_id'] ) ? null : $record['item_id'],
                    'menu'           => $record['menu'], // id or slug
                    'page'           => $record['page'],
                    'prices'         => $record['prices'],
                    'title'          => $record['title'],
                    'type'           => $record['type'],
                ] );
            }

//            print_r( ['record' => $record] );

            $conn->commit();

            return TaskResult::success( "Loaded: {$src}", ['job' => $job] );

        } catch (Exception $e) {
            $conn->rollBack();
            return TaskResult::failure( "Load failed: " . $e->getMessage() );
        }
    }

    protected function saveFile( string $src ): ?string {
        /* return null on failure */
        $dst = Filesystem::secureFilename( '.csv', 'job-' );

        $fs = Filesystem::get();
        if ( $fs->exists( $src ) && $fs->copy( $src, $dst ) ) {

        }
//        $fs = Filesystem::get();
        return $path;
    }
}