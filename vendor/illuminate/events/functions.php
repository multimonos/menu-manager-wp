<?php

namespace MenuManager\Vendor\Illuminate\Events;

use Closure;
if (!\function_exists('MenuManager\\Vendor\\Illuminate\\Events\\queueable')) {
    /**
     * Create a new queued Closure event listener.
     *
     * @param  \Closure  $closure
     * @return \Illuminate\Events\QueuedClosure
     */
    function queueable(Closure $closure)
    {
        return new \MenuManager\Vendor\Illuminate\Events\QueuedClosure($closure);
    }
}
