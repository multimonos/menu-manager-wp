<?php

namespace MenuManager\Tasks\Generic;

use MenuManager\Service\Database;
use MenuManager\Tasks\TaskResult;
use MenuManager\Wpcli\Util\CliHelper;
use WP_CLI;

class ListPostsTask {
    public static function run( string $model_class, array $fields, string $format ): TaskResult {

        Database::load();

        switch ( $format ) {
            case 'count':
                return TaskResult::success( "Ok", $model_class::count() );
                break;

            case 'ids':
                $models = $model_class::all();
                $ids = array_column( $models, 'id' );
                return TaskResult::success( "Ok", join( ' ', $ids ) );
                break;

            case 'json':
                $json = json_encode( array_map( fn( $x ) => $x->toArray(), $model_class::all() ) );
                return TaskResult::success( "Ok", $json );
                break;

            default:
            case 'table':

                $data = array_map(
                    function ( $model ) use ( $fields ) {
                        $in = $model->post->to_array();
                        $out = [];
                        foreach ( $fields as $k ) {
                            $out[$k] = $in[$k] ?? '';
                        }
                        return $out;
                    },
                    $model_class::all()
                );

                $widths = CliHelper::columnPads( $fields, $data );

                $table = CliHelper::table(
                    $widths,
                    $fields,
                    $data,
                );

                return TaskResult::success( "Ok", $table );
                break;
        }
    }
}