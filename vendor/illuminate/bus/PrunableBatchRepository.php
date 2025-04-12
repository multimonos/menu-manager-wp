<?php

namespace MenuManager\Vendor\Illuminate\Bus;

use DateTimeInterface;
interface PrunableBatchRepository extends \MenuManager\Vendor\Illuminate\Bus\BatchRepository
{
    /**
     * Prune all of the entries older than the given date.
     *
     * @param  \DateTimeInterface  $before
     * @return int
     */
    public function prune(DateTimeInterface $before);
}
