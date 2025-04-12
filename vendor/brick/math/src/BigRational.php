<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Brick\Math;

use MenuManager\Vendor\Brick\Math\Exception\DivisionByZeroException;
use MenuManager\Vendor\Brick\Math\Exception\MathException;
use MenuManager\Vendor\Brick\Math\Exception\NumberFormatException;
use MenuManager\Vendor\Brick\Math\Exception\RoundingNecessaryException;
use MenuManager\Vendor\Override;
/**
 * An arbitrarily large rational number.
 *
 * This class is immutable.
 *
 * @psalm-immutable
 */
final class BigRational extends \MenuManager\Vendor\Brick\Math\BigNumber
{
    /**
     * The numerator.
     */
    private readonly \MenuManager\Vendor\Brick\Math\BigInteger $numerator;
    /**
     * The denominator. Always strictly positive.
     */
    private readonly \MenuManager\Vendor\Brick\Math\BigInteger $denominator;
    /**
     * Protected constructor. Use a factory method to obtain an instance.
     *
     * @param BigInteger $numerator        The numerator.
     * @param BigInteger $denominator      The denominator.
     * @param bool       $checkDenominator Whether to check the denominator for negative and zero.
     *
     * @throws DivisionByZeroException If the denominator is zero.
     */
    protected function __construct(\MenuManager\Vendor\Brick\Math\BigInteger $numerator, \MenuManager\Vendor\Brick\Math\BigInteger $denominator, bool $checkDenominator)
    {
        if ($checkDenominator) {
            if ($denominator->isZero()) {
                throw DivisionByZeroException::denominatorMustNotBeZero();
            }
            if ($denominator->isNegative()) {
                $numerator = $numerator->negated();
                $denominator = $denominator->negated();
            }
        }
        $this->numerator = $numerator;
        $this->denominator = $denominator;
    }
    /**
     * @psalm-pure
     */
    #[\Override]
    protected static function from(\MenuManager\Vendor\Brick\Math\BigNumber $number) : static
    {
        return $number->toBigRational();
    }
    /**
     * Creates a BigRational out of a numerator and a denominator.
     *
     * If the denominator is negative, the signs of both the numerator and the denominator
     * will be inverted to ensure that the denominator is always positive.
     *
     * @param BigNumber|int|float|string $numerator   The numerator. Must be convertible to a BigInteger.
     * @param BigNumber|int|float|string $denominator The denominator. Must be convertible to a BigInteger.
     *
     * @throws NumberFormatException      If an argument does not represent a valid number.
     * @throws RoundingNecessaryException If an argument represents a non-integer number.
     * @throws DivisionByZeroException    If the denominator is zero.
     *
     * @psalm-pure
     */
    public static function nd(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $numerator, \MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $denominator) : \MenuManager\Vendor\Brick\Math\BigRational
    {
        $numerator = \MenuManager\Vendor\Brick\Math\BigInteger::of($numerator);
        $denominator = \MenuManager\Vendor\Brick\Math\BigInteger::of($denominator);
        return new \MenuManager\Vendor\Brick\Math\BigRational($numerator, $denominator, \true);
    }
    /**
     * Returns a BigRational representing zero.
     *
     * @psalm-pure
     */
    public static function zero() : \MenuManager\Vendor\Brick\Math\BigRational
    {
        /**
         * @psalm-suppress ImpureStaticVariable
         * @var BigRational|null $zero
         */
        static $zero;
        if ($zero === null) {
            $zero = new \MenuManager\Vendor\Brick\Math\BigRational(\MenuManager\Vendor\Brick\Math\BigInteger::zero(), \MenuManager\Vendor\Brick\Math\BigInteger::one(), \false);
        }
        return $zero;
    }
    /**
     * Returns a BigRational representing one.
     *
     * @psalm-pure
     */
    public static function one() : \MenuManager\Vendor\Brick\Math\BigRational
    {
        /**
         * @psalm-suppress ImpureStaticVariable
         * @var BigRational|null $one
         */
        static $one;
        if ($one === null) {
            $one = new \MenuManager\Vendor\Brick\Math\BigRational(\MenuManager\Vendor\Brick\Math\BigInteger::one(), \MenuManager\Vendor\Brick\Math\BigInteger::one(), \false);
        }
        return $one;
    }
    /**
     * Returns a BigRational representing ten.
     *
     * @psalm-pure
     */
    public static function ten() : \MenuManager\Vendor\Brick\Math\BigRational
    {
        /**
         * @psalm-suppress ImpureStaticVariable
         * @var BigRational|null $ten
         */
        static $ten;
        if ($ten === null) {
            $ten = new \MenuManager\Vendor\Brick\Math\BigRational(\MenuManager\Vendor\Brick\Math\BigInteger::ten(), \MenuManager\Vendor\Brick\Math\BigInteger::one(), \false);
        }
        return $ten;
    }
    public function getNumerator() : \MenuManager\Vendor\Brick\Math\BigInteger
    {
        return $this->numerator;
    }
    public function getDenominator() : \MenuManager\Vendor\Brick\Math\BigInteger
    {
        return $this->denominator;
    }
    /**
     * Returns the quotient of the division of the numerator by the denominator.
     */
    public function quotient() : \MenuManager\Vendor\Brick\Math\BigInteger
    {
        return $this->numerator->quotient($this->denominator);
    }
    /**
     * Returns the remainder of the division of the numerator by the denominator.
     */
    public function remainder() : \MenuManager\Vendor\Brick\Math\BigInteger
    {
        return $this->numerator->remainder($this->denominator);
    }
    /**
     * Returns the quotient and remainder of the division of the numerator by the denominator.
     *
     * @return BigInteger[]
     *
     * @psalm-return array{BigInteger, BigInteger}
     */
    public function quotientAndRemainder() : array
    {
        return $this->numerator->quotientAndRemainder($this->denominator);
    }
    /**
     * Returns the sum of this number and the given one.
     *
     * @param BigNumber|int|float|string $that The number to add.
     *
     * @throws MathException If the number is not valid.
     */
    public function plus(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $that) : \MenuManager\Vendor\Brick\Math\BigRational
    {
        $that = \MenuManager\Vendor\Brick\Math\BigRational::of($that);
        $numerator = $this->numerator->multipliedBy($that->denominator);
        $numerator = $numerator->plus($that->numerator->multipliedBy($this->denominator));
        $denominator = $this->denominator->multipliedBy($that->denominator);
        return new \MenuManager\Vendor\Brick\Math\BigRational($numerator, $denominator, \false);
    }
    /**
     * Returns the difference of this number and the given one.
     *
     * @param BigNumber|int|float|string $that The number to subtract.
     *
     * @throws MathException If the number is not valid.
     */
    public function minus(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $that) : \MenuManager\Vendor\Brick\Math\BigRational
    {
        $that = \MenuManager\Vendor\Brick\Math\BigRational::of($that);
        $numerator = $this->numerator->multipliedBy($that->denominator);
        $numerator = $numerator->minus($that->numerator->multipliedBy($this->denominator));
        $denominator = $this->denominator->multipliedBy($that->denominator);
        return new \MenuManager\Vendor\Brick\Math\BigRational($numerator, $denominator, \false);
    }
    /**
     * Returns the product of this number and the given one.
     *
     * @param BigNumber|int|float|string $that The multiplier.
     *
     * @throws MathException If the multiplier is not a valid number.
     */
    public function multipliedBy(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $that) : \MenuManager\Vendor\Brick\Math\BigRational
    {
        $that = \MenuManager\Vendor\Brick\Math\BigRational::of($that);
        $numerator = $this->numerator->multipliedBy($that->numerator);
        $denominator = $this->denominator->multipliedBy($that->denominator);
        return new \MenuManager\Vendor\Brick\Math\BigRational($numerator, $denominator, \false);
    }
    /**
     * Returns the result of the division of this number by the given one.
     *
     * @param BigNumber|int|float|string $that The divisor.
     *
     * @throws MathException If the divisor is not a valid number, or is zero.
     */
    public function dividedBy(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $that) : \MenuManager\Vendor\Brick\Math\BigRational
    {
        $that = \MenuManager\Vendor\Brick\Math\BigRational::of($that);
        $numerator = $this->numerator->multipliedBy($that->denominator);
        $denominator = $this->denominator->multipliedBy($that->numerator);
        return new \MenuManager\Vendor\Brick\Math\BigRational($numerator, $denominator, \true);
    }
    /**
     * Returns this number exponentiated to the given value.
     *
     * @throws \InvalidArgumentException If the exponent is not in the range 0 to 1,000,000.
     */
    public function power(int $exponent) : \MenuManager\Vendor\Brick\Math\BigRational
    {
        if ($exponent === 0) {
            $one = \MenuManager\Vendor\Brick\Math\BigInteger::one();
            return new \MenuManager\Vendor\Brick\Math\BigRational($one, $one, \false);
        }
        if ($exponent === 1) {
            return $this;
        }
        return new \MenuManager\Vendor\Brick\Math\BigRational($this->numerator->power($exponent), $this->denominator->power($exponent), \false);
    }
    /**
     * Returns the reciprocal of this BigRational.
     *
     * The reciprocal has the numerator and denominator swapped.
     *
     * @throws DivisionByZeroException If the numerator is zero.
     */
    public function reciprocal() : \MenuManager\Vendor\Brick\Math\BigRational
    {
        return new \MenuManager\Vendor\Brick\Math\BigRational($this->denominator, $this->numerator, \true);
    }
    /**
     * Returns the absolute value of this BigRational.
     */
    public function abs() : \MenuManager\Vendor\Brick\Math\BigRational
    {
        return new \MenuManager\Vendor\Brick\Math\BigRational($this->numerator->abs(), $this->denominator, \false);
    }
    /**
     * Returns the negated value of this BigRational.
     */
    public function negated() : \MenuManager\Vendor\Brick\Math\BigRational
    {
        return new \MenuManager\Vendor\Brick\Math\BigRational($this->numerator->negated(), $this->denominator, \false);
    }
    /**
     * Returns the simplified value of this BigRational.
     */
    public function simplified() : \MenuManager\Vendor\Brick\Math\BigRational
    {
        $gcd = $this->numerator->gcd($this->denominator);
        $numerator = $this->numerator->quotient($gcd);
        $denominator = $this->denominator->quotient($gcd);
        return new \MenuManager\Vendor\Brick\Math\BigRational($numerator, $denominator, \false);
    }
    #[\Override]
    public function compareTo(\MenuManager\Vendor\Brick\Math\BigNumber|int|float|string $that) : int
    {
        return $this->minus($that)->getSign();
    }
    #[\Override]
    public function getSign() : int
    {
        return $this->numerator->getSign();
    }
    #[\Override]
    public function toBigInteger() : \MenuManager\Vendor\Brick\Math\BigInteger
    {
        $simplified = $this->simplified();
        if (!$simplified->denominator->isEqualTo(1)) {
            throw new RoundingNecessaryException('This rational number cannot be represented as an integer value without rounding.');
        }
        return $simplified->numerator;
    }
    #[\Override]
    public function toBigDecimal() : \MenuManager\Vendor\Brick\Math\BigDecimal
    {
        return $this->numerator->toBigDecimal()->exactlyDividedBy($this->denominator);
    }
    #[\Override]
    public function toBigRational() : \MenuManager\Vendor\Brick\Math\BigRational
    {
        return $this;
    }
    #[\Override]
    public function toScale(int $scale, \MenuManager\Vendor\Brick\Math\RoundingMode $roundingMode = \MenuManager\Vendor\Brick\Math\RoundingMode::UNNECESSARY) : \MenuManager\Vendor\Brick\Math\BigDecimal
    {
        return $this->numerator->toBigDecimal()->dividedBy($this->denominator, $scale, $roundingMode);
    }
    #[\Override]
    public function toInt() : int
    {
        return $this->toBigInteger()->toInt();
    }
    #[\Override]
    public function toFloat() : float
    {
        $simplified = $this->simplified();
        return $simplified->numerator->toFloat() / $simplified->denominator->toFloat();
    }
    #[\Override]
    public function __toString() : string
    {
        $numerator = (string) $this->numerator;
        $denominator = (string) $this->denominator;
        if ($denominator === '1') {
            return $numerator;
        }
        return $numerator . '/' . $denominator;
    }
    /**
     * This method is required for serializing the object and SHOULD NOT be accessed directly.
     *
     * @internal
     *
     * @return array{numerator: BigInteger, denominator: BigInteger}
     */
    public function __serialize() : array
    {
        return ['numerator' => $this->numerator, 'denominator' => $this->denominator];
    }
    /**
     * This method is only here to allow unserializing the object and cannot be accessed directly.
     *
     * @internal
     * @psalm-suppress RedundantPropertyInitializationCheck
     *
     * @param array{numerator: BigInteger, denominator: BigInteger} $data
     *
     * @throws \LogicException
     */
    public function __unserialize(array $data) : void
    {
        if (isset($this->numerator)) {
            throw new \LogicException('__unserialize() is an internal function, it must not be called directly.');
        }
        $this->numerator = $data['numerator'];
        $this->denominator = $data['denominator'];
    }
}
