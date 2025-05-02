<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Model\Node;
use MenuManager\Service\Database;
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

                $data = Node::all()->transform( function ( $x ) {
                    return [
                        'id'    => $x->id,
                        'type'  => $x->type->value,
                        'title' => $x->title,
                    ];
                } )->toArray();


                $widths = CliOutput::maxLengths( $data );

                CliOutput::table(
                    $widths,
                    ['id', 'type', 'title'],
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
        Database::load();

        $id = $args[0];

        $node = Node::find( $id );

        // failed
        if ( $node === null ) {
            WP_CLI::error( "Node not found id=" . $id );
        }

        if ( ! $node->delete() ) {
            WP_CLI::error( "Failed to delete Node id=" . $id );
        }

        WP_CLI::success( 'Node deleted id=' . $id );
    }
}
