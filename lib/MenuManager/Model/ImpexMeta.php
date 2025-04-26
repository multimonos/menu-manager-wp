<?php

namespace MenuManager\Model;

use MenuManager\Vendor\Illuminate\Support\Collection;

class ImpexMeta {
    public Collection $jobId;
    public $rowCount;
    public Collection $menuIds;
    public Collection $actions;
    public Collection $types;
    public Collection $imageIds;

    public static function analyze( Collection $rows ) {
        $meta = new self;

        echo "\n----------------------analyze";
        $meta->jobId = $rows->pluck( 'job_id' )->unique();
        $meta->rowCount = $rows->count();
        $meta->menuIds = $rows->pluck( 'menu' )->unique()->filter();
        $meta->actions = $rows->pluck( 'action' )->unique()->filter();
        $meta->types = $rows->pluck( 'type' )->unique()->filter();
        $meta->imageIds = $rows->pluck( 'image_ids' )->unique()->flatMap( fn( $x ) => explode( '|', $x ) )->unique()->filter();

        return $meta;
    }
}