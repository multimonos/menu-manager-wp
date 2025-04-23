<?php

declare (strict_types=1);
namespace MenuManager\Vendor\ZipStream\Exception;

use MenuManager\Vendor\ZipStream\Exception;
/**
 * This Exception gets invoked if a file wasn't found
 */
class FileNotFoundException extends Exception
{
    /**
     * @internal
     */
    public function __construct(public readonly string $path)
    {
        parent::__construct("The file with the path {$path} wasn't found.");
    }
}
