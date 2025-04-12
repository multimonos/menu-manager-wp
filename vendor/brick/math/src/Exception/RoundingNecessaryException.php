<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Brick\Math\Exception;

/**
 * Exception thrown when a number cannot be represented at the requested scale without rounding.
 */
class RoundingNecessaryException extends \MenuManager\Vendor\Brick\Math\Exception\MathException
{
    /**
     * @psalm-pure
     */
    public static function roundingNecessary() : \MenuManager\Vendor\Brick\Math\Exception\RoundingNecessaryException
    {
        return new self('Rounding is necessary to represent the result of the operation at this scale.');
    }
}
