<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Database\db;
use MenuManager\Database\Model\Node;
use MenuManager\Wpcli\CliOutput;
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
    public function list( $args, $assoc_args ) {
        $format = $assoc_args['format'] ?? 'table';

        db::load();

        switch ( $format ) {
            case 'count':
                WP_CLI::line( Node::all()->pluck( 'id' )->count() );
                break;

            case 'ids':
                $ids = Node::all()->pluck( 'id' )->join( ' ' );
                WP_CLI::line( $ids );
                break;

            default:
            case 'table':

                $data = Node::all()->transform( function ( $x ) {
                    return [
                        'id'    => $x->id,
                        'type'  => $x->type,
                        'title' => $x->title,
                    ];
                } )->toArray();

                $maxlen = array_reduce(
                    $data,
                    fn( $max, $item ) => max( $max, strlen( $item['title'] ) ),
                    0
                );

                CliOutput::table(
                    [6, 16, $maxlen],
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
     * : The id of the node ot get.
     *
     * @when after_wp_load
     */
    public function get( $args, $assoc_args ) {
        db::load();

        $id = $args[0];

        $node = Node::find( $id );

        // failed
        if ( $node === null ) {
            WP_CLI::error( "Node not found id=" . $id );
        }

        // ok
        echo json_encode( [
            "Node"     => $node->toArray(),
            "NodeMeta" => $node->meta->toArray(),
        ] );
    }

}
