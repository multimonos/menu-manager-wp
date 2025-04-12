<?php

namespace MenuManager\Vendor\Illuminate\Contracts\Filesystem;

interface Cloud extends \MenuManager\Vendor\Illuminate\Contracts\Filesystem\Filesystem
{
    /**
     * Get the URL for the file at the given path.
     *
     * @param  string  $path
     * @return string
     */
    public function url($path);
}
