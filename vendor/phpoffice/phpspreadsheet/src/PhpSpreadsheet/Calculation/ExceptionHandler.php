<?php

namespace MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation;

class ExceptionHandler
{
    /**
     * Register errorhandler.
     */
    public function __construct()
    {
        /** @var callable $callable */
        $callable = [\MenuManager\Vendor\PhpOffice\PhpSpreadsheet\Calculation\Exception::class, 'errorHandlerCallback'];
        \set_error_handler($callable, \E_ALL);
    }
    /**
     * Unregister errorhandler.
     */
    public function __destruct()
    {
        \restore_error_handler();
    }
}
