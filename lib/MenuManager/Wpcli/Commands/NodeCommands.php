<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Model\Node;
use MenuManager\Service\Database;
use MenuManager\Tasks\Generic\DeleteModelTask;
use MenuManager\Tasks\Generic\GetModelTask;
use MenuManager\Wpcli\CliOutput;
use MenuManager\Wpcli\Util\CommandHelper;
use WP_CLI;

class NodeCommands {

    /**
     * List nodes.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format. Options: table, ids. Default: table.
     *
     * ## EXAMPLES
     *
     *      wp mm node list
     *
     * @when after_wp_load
     */
    public function ls( $args, $assoc_args ) {
        $format = $assoc_args['format'] ?? 'table';

        Database::load();

        switch ( $format ) {
            case 'count':
                WP_CLI::line( Node::query()->count() );
                break;

            case 'ids':
                $ids = Node::all()->pluck( 'id' )->join( ' ' );
                WP_CLI::line( $ids );
                break;

            case 'json':
                WP_CLI::line( Node::all()->toJson() );
                break;

            default:
            case 'table':
                if ( Node::query()->count() === 0 ) {
                    WP_CLI::success( "No records found." );
                    return;
                }

                $fields = [
                    'id',
                    'type',
                    'title',
                ];
                $data = Node::all()->map( fn( $model ) => $model->only( $fields ) )->toArray();

                $widths = CliOutput::columnPads( $fields, $data );

                CliOutput::table(
                    $widths,
                    $fields,
                    $data,
                );
                break;
        }
    }


    /**
     * Get details about a node.
     *
     * ## OPTIONS
     *
     * <id>
     * : The id of the node.
     *
     * @when after_wp_load
     */
    public function get( $args, $assoc_args ) {
        $id = intval( $args[0] ?? 0 );

        $task = new GetModelTask();
        $rs = $task->run( Node::class, $id );
        CommandHelper::sendTaskResultAsJson( $rs );
    }


    /**
     * Delete a node.
     *
     * ## OPTIONS
     *
     * <id>
     * : The id of the node.
     *
     * @when after_wp_load
     */
    public function rm( $args, $assoc_args ) {
        $id = intval( $args[0] ?? -1 );

        $task = new DeleteModelTask();
        $rs = $task->run( Node::class, $id );
        CommandHelper::sendTaskResult( $rs );
    }
}
