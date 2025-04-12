<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Brick\Math;

use MenuManager\Vendor\Brick\Math\Exception\DivisionByZeroException;
use MenuManager\Vendor\Brick\Math\Exception\MathException;
use MenuManager\Vendor\Brick\Math\Exception\NumberFormatException;
use MenuManager\Vendor\Brick\Math\Exception\RoundingNecessaryException;
use MenuManager\Vendor\Override;
/**
 * Common interface for arbitrary-precision rational numbers.
 *
 * @psalm-immutable
 */
abstract class BigNumber implements \JsonSerializable
{
    /**
     * The regular expression used to parse integer or decimal numbers.
     */
    private const PARSE_REGEXP_NUMERICAL = '/^' . '(?<sign>[\\-\\+])?' . '(?<integral>[0-9]+)?' . '(?<point>\\.)?' . '(?<fractional>[0-9]+)?' . '(?:[eE](?<exponent>[\\-\\+]?[0-9]+))?' . '$/';
    /**
     * The regular expression used to parse rational numbers.
     */
    private const PARSE_REGEXP_RATIONAL = '/^' . '(?<sign>[\\-\\+])?' . '(?<numerator>[0-9]+)' . '\\/?' . '(?<denominator>[0-9]+)' . '$/';
    /**
     * Creates a BigNumber of the given value.
     *
     * The concrete return type is dependent on the given value, with the following rules:
     *
     * - BigNumber instances are returned as is
     * - integer numbers are returned as BigInteger
     * - floating point numbers are converted to a string then parsed as such
     * - strings containing a `/` character are returned as BigRational
     * - strings containing a `.` character or using an exponential notation are returned as BigDecimal
     * - strings containing only digits with an optional leading `+` or `-` sign are returned as BigInteger
     *
     * @throws NumberFormatException If the format of the number is not valid.
     * @throws DivisionByZeroException If the value represents a rational number with a denominator of zero.
     * @throws RoundingNecessaryException If the value cannot be converted to an instance of the subclass without rounding.
     *
     * @psalm-pure
     */
    public static final function of(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $value) : static
    {
        $value = self::_of($value);
        if (static::class === \MenuManager\Vendor\Brick\Math\BigNumber::class) {
            // https://github.com/vimeo/psalm/issues/10309
            \assert($value instanceof static);
            return $value;
        }
        return static::from($value);
    }
    /**
     * @throws NumberFormatException If the format of the number is not valid.
     * @throws DivisionByZeroException If the value represents a rational number with a denominator of zero.
     *
     * @psalm-pure
     */
    private static function _of(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $value) : \MenuManager\Vendor\Brick\Math\BigNumber
    {
        if ($value instanceof \MenuManager\Vendor\Brick\Math\BigNumber) {
            return $value;
        }
        if (\is_int($value)) {
            return new \MenuManager\Vendor\Brick\Math\BigInteger((string) $value);
        }
        if (\is_float($value)) {
            $value = (string) $value;
        }
        if (\str_contains($value, '/')) {
            // Rational number
            if (\preg_match(self::PARSE_REGEXP_RATIONAL, $value, $matches, \PREG_UNMATCHED_AS_NULL) !== 1) {
                throw NumberFormatException::invalidFormat($value);
            }
            $sign = $matches['sign'];
            $numerator = $matches['numerator'];
            $denominator = $matches['denominator'];
            \assert($numerator !== null);
            \assert($denominator !== null);
            $numerator = self::cleanUp($sign, $numerator);
            $denominator = self::cleanUp(null, $denominator);
            if ($denominator === '0') {
                throw DivisionByZeroException::denominatorMustNotBeZero();
            }
            return new \MenuManager\Vendor\Brick\Math\BigRational(new \MenuManager\Vendor\Brick\Math\BigInteger($numerator), new \MenuManager\Vendor\Brick\Math\BigInteger($denominator), \false);
        } else {
            // Integer or decimal number
            if (\preg_match(self::PARSE_REGEXP_NUMERICAL, $value, $matches, \PREG_UNMATCHED_AS_NULL) !== 1) {
                throw NumberFormatException::invalidFormat($value);
            }
            $sign = $matches['sign'];
            $point = $matches['point'];
            $integral = $matches['integral'];
            $fractional = $matches['fractional'];
            $exponent = $matches['exponent'];
            if ($integral === null && $fractional === null) {
                throw NumberFormatException::invalidFormat($value);
            }
            if ($integral === null) {
                $integral = '0';
            }
            if ($point !== null || $exponent !== null) {
                $fractional = $fractional ?? '';
                $exponent = $exponent !== null ? (int) $exponent : 0;
                if ($exponent === \PHP_INT_MIN || $exponent === \PHP_INT_MAX) {
                    throw new NumberFormatException('Exponent too large.');
                }
                $unscaledValue = self::cleanUp($sign, $integral . $fractional);
                $scale = \strlen($fractional) - $exponent;
                if ($scale < 0) {
                    if ($unscaledValue !== '0') {
                        $unscaledValue .= \str_repeat('0', -$scale);
                    }
                    $scale = 0;
                }
                return new \MenuManager\Vendor\Brick\Math\BigDecimal($unscaledValue, $scale);
            }
            $integral = self::cleanUp($sign, $integral);
            return new \MenuManager\Vendor\Brick\Math\BigInteger($integral);
        }
    }
    /**
     * Overridden by subclasses to convert a BigNumber to an instance of the subclass.
     *
     * @throws RoundingNecessaryException If the value cannot be converted.
     *
     * @psalm-pure
     */
    protected static abstract function from(\MenuManager\Vendor\Brick\Math\BigNumber $number) : static;
    /**
     * Proxy method to access BigInteger's protected constructor from sibling classes.
     *
     * @internal
     * @psalm-pure
     */
    protected final function newBigInteger(string $value) : \MenuManager\Vendor\Brick\Math\BigInteger
    {
        return new \MenuManager\Vendor\Brick\Math\BigInteger($value);
    }
    /**
     * Proxy method to access BigDecimal's protected constructor from sibling classes.
     *
     * @internal
     * @psalm-pure
     */
    protected final function newBigDecimal(string $value, int $scale = 0) : \MenuManager\Vendor\Brick\Math\BigDecimal
    {
        return new \MenuManager\Vendor\Brick\Math\BigDecimal($value, $scale);
    }
    /**
     * Proxy method to access BigRational's protected constructor from sibling classes.
     *
     * @internal
     * @psalm-pure
     */
    protected final function newBigRational(\MenuManager\Vendor\Brick\Math\BigInteger $numerator, \MenuManager\Vendor\Brick\Math\BigInteger $denominator, bool $checkDenominator) : \MenuManager\Vendor\Brick\Math\BigRational
    {
        return new \MenuManager\Vendor\Brick\Math\BigRational($numerator, $denominator, $checkDenominator);
    }
    /**
     * Returns the minimum of the given values.
     *
     * @param BigNumber|int|float|string ...$values The numbers to compare. All the numbers need to be convertible
     *                                              to an instance of the class this method is called on.
     *
     * @throws \InvalidArgumentException If no values are given.
     * @throws MathException             If an argument is not valid.
     *
     * @psalm-pure
     */
    public static final function min(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string ...$values) : static
    {
        $min = null;
        foreach ($values as $value) {
            $value = static::of($value);
            if ($min === null || $value->isLessThan($min)) {
                $min = $value;
            }
        }
        if ($min === null) {
            throw new \InvalidArgumentException(__METHOD__ . '() expects at least one value.');
        }
        return $min;
    }
    /**
     * Returns the maximum of the given values.
     *
     * @param BigNumber|int|float|string ...$values The numbers to compare. All the numbers need to be convertible
     *                                              to an instance of the class this method is called on.
     *
     * @throws \InvalidArgumentException If no values are given.
     * @throws MathException             If an argument is not valid.
     *
     * @psalm-pure
     */
    public static final function max(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string ...$values) : static
    {
        $max = null;
        foreach ($values as $value) {
            $value = static::of($value);
            if ($max === null || $value->isGreaterThan($max)) {
                $max = $value;
            }
        }
        if ($max === null) {
            throw new \InvalidArgumentException(__METHOD__ . '() expects at least one value.');
        }
        return $max;
    }
    /**
     * Returns the sum of the given values.
     *
     * @param BigNumber|int|float|string ...$values The numbers to add. All the numbers need to be convertible
     *                                              to an instance of the class this method is called on.
     *
     * @throws \InvalidArgumentException If no values are given.
     * @throws MathException             If an argument is not valid.
     *
     * @psalm-pure
     */
    public static final function sum(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string ...$values) : static
    {
        /** @var static|null $sum */
        $sum = null;
        foreach ($values as $value) {
            $value = static::of($value);
            $sum = $sum === null ? $value : self::add($sum, $value);
        }
        if ($sum === null) {
            throw new \InvalidArgumentException(__METHOD__ . '() expects at least one value.');
        }
        return $sum;
    }
    /**
     * Adds two BigNumber instances in the correct order to avoid a RoundingNecessaryException.
     *
     * @todo This could be better resolved by creating an abstract protected method in BigNumber, and leaving to
     *       concrete classes the responsibility to perform the addition themselves or delegate it to the given number,
     *       depending on their ability to perform the operation. This will also require a version bump because we're
     *       potentially breaking custom BigNumber implementations (if any...)
     *
     * @psalm-pure
     */
    private static function add(\MenuManager\Vendor\Brick\Math\BigNumber $a, \MenuManager\Vendor\Brick\Math\BigNumber $b) : \MenuManager\Vendor\Brick\Math\BigNumber
    {
        if ($a instanceof \MenuManager\Vendor\Brick\Math\BigRational) {
            return $a->plus($b);
        }
        if ($b instanceof \MenuManager\Vendor\Brick\Math\BigRational) {
            return $b->plus($a);
        }
        if ($a instanceof \MenuManager\Vendor\Brick\Math\BigDecimal) {
            return $a->plus($b);
        }
        if ($b instanceof \MenuManager\Vendor\Brick\Math\BigDecimal) {
            return $b->plus($a);
        }
        /** @var BigInteger $a */
        return $a->plus($b);
    }
    /**
     * Removes optional leading zeros and applies sign.
     *
     * @param string|null $sign   The sign, '+' or '-', optional. Null is allowed for convenience and treated as '+'.
     * @param string      $number The number, validated as a non-empty string of digits.
     *
     * @psalm-pure
     */
    private static function cleanUp(string|null $sign, string $number) : string
    {
        $number = \ltrim($number, '0');
        if ($number === '') {
            return '0';
        }
        return $sign === '-' ? '-' . $number : $number;
    }
    /**
     * Checks if this number is equal to the given one.
     */
    public final function isEqualTo(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $that) : bool
    {
        return $this->compareTo($that) === 0;
    }
    /**
     * Checks if this number is strictly lower than the given one.
     */
    public final function isLessThan(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $that) : bool
    {
        return $this->compareTo($that) < 0;
    }
    /**
     * Checks if this number is lower than or equal to the given one.
     */
    public final function isLessThanOrEqualTo(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $that) : bool
    {
        return $this->compareTo($that) <= 0;
    }
    /**
     * Checks if this number is strictly greater than the given one.
     */
    public final function isGreaterThan(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $that) : bool
    {
        return $this->compareTo($that) > 0;
    }
    /**
     * Checks if this number is greater than or equal to the given one.
     */
    public final function isGreaterThanOrEqualTo(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $that) : bool
    {
        return $this->compareTo($that) >= 0;
    }
    /**
     * Checks if this number equals zero.
     */
    public final function isZero() : bool
    {
        return $this->getSign() === 0;
    }
    /**
     * Checks if this number is strictly negative.
     */
    public final function isNegative() : bool
    {
        return $this->getSign() < 0;
    }
    /**
     * Checks if this number is negative or zero.
     */
    public final function isNegativeOrZero() : bool
    {
        return $this->getSign() <= 0;
    }
    /**
     * Checks if this number is strictly positive.
     */
    public final function isPositive() : bool
    {
        return $this->getSign() > 0;
    }
    /**
     * Checks if this number is positive or zero.
     */
    public final function isPositiveOrZero() : bool
    {
        return $this->getSign() >= 0;
    }
    /**
     * Returns the sign of this number.
     *
     * @psalm-return -1|0|1
     *
     * @return int -1 if the number is negative, 0 if zero, 1 if positive.
     */
    public abstract function getSign() : int;
    /**
     * Compares this number to the given one.
     *
     * @psalm-return -1|0|1
     *
     * @return int -1 if `$this` is lower than, 0 if equal to, 1 if greater than `$that`.
     *
     * @throws MathException If the number is not valid.
     */
    public abstract function compareTo(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $that) : int;
    /**
     * Converts this number to a BigInteger.
     *
     * @throws RoundingNecessaryException If this number cannot be converted to a BigInteger without rounding.
     */
    public abstract function toBigInteger() : \MenuManager\Vendor\Brick\Math\BigInteger;
    /**
     * Converts this number to a BigDecimal.
     *
     * @throws RoundingNecessaryException If this number cannot be converted to a BigDecimal without rounding.
     */
    public abstract function toBigDecimal() : \MenuManager\Vendor\Brick\Math\BigDecimal;
    /**
     * Converts this number to a BigRational.
     */
    public abstract function toBigRational() : \MenuManager\Vendor\Brick\Math\BigRational;
    /**
     * Converts this number to a BigDecimal with the given scale, using rounding if necessary.
     *
     * @param int          $scale        The scale of the resulting `BigDecimal`.
     * @param RoundingMode $roundingMode An optional rounding mode, defaults to UNNECESSARY.
     *
     * @throws RoundingNecessaryException If this number cannot be converted to the given scale without rounding.
     *                                    This only applies when RoundingMode::UNNECESSARY is used.
     */
    public abstract function toScale(int $scale, \MenuManager\Vendor\Brick\Math\RoundingMode $roundingMode = \MenuManager\Vendor\Brick\Math\RoundingMode::UNNECESSARY) : \MenuManager\Vendor\Brick\Math\BigDecimal;
    /**
     * Returns the exact value of this number as a native integer.
     *
     * If this number cannot be converted to a native integer without losing precision, an exception is thrown.
     * Note that the acceptable range for an integer depends on the platform and differs for 32-bit and 64-bit.
     *
     * @throws MathException If this number cannot be exactly converted to a native integer.
     */
    public abstract function toInt() : int;
    /**
     * Returns an approximation of this number as a floating-point value.
     *
     * Note that this method can discard information as the precision of a floating-point value
     * is inherently limited.
     *
     * If the number is greater than the largest representable floating point number, positive infinity is returned.
     * If the number is less than the smallest representable floating point number, negative infinity is returned.
     */
    public abstract function toFloat() : float;
    /**
     * Returns a string representation of this number.
     *
     * The output of this method can be parsed by the `of()` factory method;
     * this will yield an object equal to this one, without any information loss.
     */
    public abstract function __toString() : string;
    #[\Override]
    public final function jsonSerialize() : string
    {
        return $this->__toString();
    }
}
