<?php

namespace MenuManager\Vendor\Illuminate\Contracts\Container;

use Exception;
use MenuManager\Vendor\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
