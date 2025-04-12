<?php

namespace MenuManager\Vendor\Psr\SimpleCache;

/**
 * Exception interface for invalid cache arguments.
 *
 * When an invalid argument is passed it must throw an exception which implements
 * this interface
 */
interface InvalidArgumentException extends \MenuManager\Vendor\Psr\SimpleCache\CacheException
{
}
