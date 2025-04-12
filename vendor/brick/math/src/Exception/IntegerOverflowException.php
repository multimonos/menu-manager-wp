<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Brick\Math\Exception;

use MenuManager\Vendor\Brick\Math\BigInteger;
/**
 * Exception thrown when an integer overflow occurs.
 */
class IntegerOverflowException extends \MenuManager\Vendor\Brick\Math\Exception\MathException
{
    /**
     * @psalm-pure
     */
    public static function toIntOverflow(BigInteger $value) : \MenuManager\Vendor\Brick\Math\Exception\IntegerOverflowException
    {
        $message = '%s is out of range %d to %d and cannot be represented as an integer.';
        return new self(\sprintf($message, (string) $value, \PHP_INT_MIN, \PHP_INT_MAX));
    }
}
