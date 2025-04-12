<?php

namespace MenuManager\Vendor\Illuminate\Database\Eloquent\Factories;

use MenuManager\Vendor\Illuminate\Support\Arr;
class CrossJoinSequence extends \MenuManager\Vendor\Illuminate\Database\Eloquent\Factories\Sequence
{
    /**
     * Create a new cross join sequence instance.
     *
     * @param  array  ...$sequences
     * @return void
     */
    public function __construct(...$sequences)
    {
        $crossJoined = \array_map(function ($a) {
            return \array_merge(...$a);
        }, Arr::crossJoin(...$sequences));
        parent::__construct(...$crossJoined);
    }
}
