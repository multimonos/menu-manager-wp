<?php

namespace MenuManager\Task;

use MenuManager\Logger;
use MenuManager\Vendor\Illuminate\Support\Collection;

class UpdateTask {
    public function run( \WP_Post $menu, Collection $items ) {

        Logger::taskInfo( 'update', 'menu=' . $menu->post_name );
        // only take action where specified
        $action_items = $items->filter( fn( $x ) => ! empty( $x->action ) );
        print_r( $action_items );
        echo "\n- count: " . $action_items->count();
    }
}