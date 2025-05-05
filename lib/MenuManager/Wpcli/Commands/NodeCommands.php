<?php

namespace MenuManager\Wpcli\Commands;


use MenuManager\Model\Node;
use MenuManager\Tasks\Generic\DeleteModelTask;
use MenuManager\Tasks\Generic\GetModelTask;
use MenuManager\Tasks\Generic\ListModelTask;
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

        $fields = [
            'id',
            'type',
            'title',
        ];

        $task = new ListModelTask();
        $rs = $task->run( Node::class, $fields, $format );

        CommandHelper::sendDataOnly( $rs );
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
