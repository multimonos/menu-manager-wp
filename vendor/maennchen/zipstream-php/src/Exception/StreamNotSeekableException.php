<?php

declare (strict_types=1);
namespace MenuManager\Vendor\ZipStream\Exception;

use MenuManager\Vendor\ZipStream\Exception;
/**
 * This Exception gets invoked if a non seekable stream is
 * provided and zero headers are disabled.
 */
class StreamNotSeekableException extends Exception
{
    /**
     * @internal
     */
    public function __construct()
    {
        parent::__construct('enableZeroHeader must be enable to add non seekable streams');
    }
}
