<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace MenuManager\Vendor\League\Csv\Serializer;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;
use Throwable;
use function class_exists;
use function is_string;
/**
 * @implements TypeCasting<DateTimeImmutable|DateTime|null>
 */
final class CastToDate implements \MenuManager\Vendor\League\Csv\Serializer\TypeCasting
{
    /** @var class-string */
    private string $class;
    private readonly bool $isNullable;
    private DateTimeImmutable|DateTime|null $default = null;
    private readonly \MenuManager\Vendor\League\Csv\Serializer\Type $type;
    private readonly \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo $info;
    private ?DateTimeZone $timezone = null;
    private ?string $format = null;
    /**
     * @throws MappingFailed
     */
    public function __construct(ReflectionProperty|ReflectionParameter $reflectionProperty)
    {
        [$this->type, $this->class, $this->isNullable] = $this->init($reflectionProperty);
        $this->info = \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo::fromAccessor($reflectionProperty);
    }
    /**
     * @param ?class-string $className
     *
     * @throws MappingFailed
     */
    public function setOptions(?string $default = null, ?string $format = null, DateTimeZone|string|null $timezone = null, ?string $className = null) : void
    {
        $this->class = match (\true) {
            !\interface_exists($this->class) && !\MenuManager\Vendor\League\Csv\Serializer\Type::Mixed->equals($this->type) => $this->class,
            DateTimeInterface::class === $this->class && null === $className => DateTimeImmutable::class,
            \interface_exists($this->class) && null !== $className && class_exists($className) && (new ReflectionClass($className))->implementsInterface($this->class) => $className,
            default => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('`' . $this->info->targetName . '` type is `' . ($this->class ?? 'mixed') . '` but the specified class via the `$className` argument is invalid or could not be found.'),
        };
        try {
            $this->format = $format;
            $this->timezone = is_string($timezone) ? new DateTimeZone($timezone) : $timezone;
            $this->default = null !== $default ? $this->cast($default) : $default;
        } catch (Throwable $exception) {
            throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('The `timezone` and/or `format` options used for `' . self::class . '` are invalud.', 0, $exception);
        }
    }
    public function info() : \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo
    {
        return $this->info;
    }
    /**
     * @throws TypeCastingFailed
     */
    public function toVariable(mixed $value) : DateTimeImmutable|DateTime|null
    {
        return match (\true) {
            null !== $value && '' !== $value => $this->cast($value),
            $this->isNullable => $this->default,
            default => throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToNotNullableType($this->class, info: $this->info),
        };
    }
    /**
     * @throws TypeCastingFailed
     */
    private function cast(mixed $value) : DateTimeImmutable|DateTime
    {
        if ($value instanceof DateTimeInterface) {
            if ($value instanceof $this->class) {
                return $value;
            }
            return $this->class::createFromInterface($value);
        }
        is_string($value) || throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToInvalidValue($value, $this->class, info: $this->info);
        try {
            $date = null !== $this->format ? $this->class::createFromFormat($this->format, $value, $this->timezone) : new $this->class($value, $this->timezone);
            if (\false === $date) {
                throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToInvalidValue($value, $this->class);
            }
        } catch (Throwable $exception) {
            if ($exception instanceof \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed) {
                throw $exception;
            }
            throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToInvalidValue($value, $this->class, $exception, $this->info);
        }
        return $date;
    }
    /**
     * @throws MappingFailed
     *
     * @return array{0:Type, 1:class-string<DateTimeInterface>, 2:bool}
     */
    private function init(ReflectionProperty|ReflectionParameter $reflectionProperty) : array
    {
        if (null === $reflectionProperty->getType()) {
            return [\MenuManager\Vendor\League\Csv\Serializer\Type::Mixed, DateTimeInterface::class, \true];
        }
        $type = null;
        $isNullable = \false;
        foreach (\MenuManager\Vendor\League\Csv\Serializer\Type::list($reflectionProperty) as $found) {
            if (!$isNullable && $found[1]->allowsNull()) {
                $isNullable = \true;
            }
            if (null === $type && $found[0]->isOneOf(\MenuManager\Vendor\League\Csv\Serializer\Type::Mixed, \MenuManager\Vendor\League\Csv\Serializer\Type::Date)) {
                $type = $found;
            }
        }
        null !== $type || throw throw \MenuManager\Vendor\League\Csv\Serializer\MappingFailed::dueToTypeCastingUnsupportedType($reflectionProperty, $this, DateTimeInterface::class, 'mixed');
        /** @var class-string<DateTimeInterface> $className */
        $className = $type[1]->getName();
        return [$type[0], $className, $isNullable];
    }
}
