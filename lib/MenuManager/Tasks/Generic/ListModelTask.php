<?php

namespace MenuManager\Tasks\Generic;

use MenuManager\Service\Database;
use MenuManager\Tasks\TaskResult;
use MenuManager\Wpcli\Util\CliHelper;
use WP_CLI;

class ListModelTask {
    public static function run( string $model_class, array $fields, string $format ): TaskResult {

        Database::load();

        switch ( $format ) {
            case 'count':
                return TaskResult::success( "Ok", $model_class::query()->count() );
                break;

            case 'ids':
                $ids = $model_class::all()->pluck( 'id' )->join( ' ' );
                return TaskResult::success( "Ok", $ids );
                break;

            case 'json':
                $json = $model_class::all()->toJson();
                return TaskResult::success( "Ok", $json );
                break;

            default:
            case 'table':

                $data = $model_class::all()->map( fn( $model ) => $model->only( $fields ) )->toArray();

                // no results
                $widths = is_null( $data )
                    ? CliHelper::columnPads( $fields, [] )
                    : CliHelper::columnPads( $fields, $data );

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