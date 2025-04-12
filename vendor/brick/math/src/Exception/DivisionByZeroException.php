<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Brick\Math\Exception;

/**
 * Exception thrown when a division by zero occurs.
 */
class DivisionByZeroException extends \MenuManager\Vendor\Brick\Math\Exception\MathException
{
    /**
     * @psalm-pure
     */
    public static function divisionByZero() : \MenuManager\Vendor\Brick\Math\Exception\DivisionByZeroException
    {
        return new self('Division by zero.');
    }
    /**
     * @psalm-pure
     */
    public static function modulusMustNotBeZero() : \MenuManager\Vendor\Brick\Math\Exception\DivisionByZeroException
    {
        return new self('The modulus must not be zero.');
    }
    /**
     * @psalm-pure
     */
    public static function denominatorMustNotBeZero() : \MenuManager\Vendor\Brick\Math\Exception\DivisionByZeroException
    {
        return new self('The denominator of a rational number cannot be zero.');
    }
}
