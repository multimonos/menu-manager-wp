<?php

namespace MenuManager\Utils;

use MenuManager\Database\Model\Node;

class NodeSortOrderManager {
    const START = 10;
    const DELTA = 10;

    protected $sort = [];

    public function setSort( Node $node ): Node {

        if ( ! isset( $this->sort[$node->parent_id] ) ) {
            $this->sort[$node->parent_id] = self::START;
        } else {
            $this->sort[$node->parent_id] += self::DELTA;
        }

        $node->sort_order = $this->sort[$node->parent_id];

        return $node;
    }
}
