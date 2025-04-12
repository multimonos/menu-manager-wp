<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MenuManager\Vendor\League\Csv\Serializer;

use DateTimeInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Throwable;
use UnitEnum;
use function class_exists;
use function enum_exists;
use function in_array;
use const FILTER_UNSAFE_RAW;
use const FILTER_VALIDATE_BOOL;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;
enum Type : string
{
    case Bool = 'bool';
    case True = 'true';
    case False = 'false';
    case Null = 'null';
    case Int = 'int';
    case Float = 'float';
    case String = 'string';
    case Mixed = 'mixed';
    case Array = 'array';
    case Iterable = 'iterable';
    case Enum = UnitEnum::class;
    case Date = DateTimeInterface::class;
    public function equals(mixed $value) : bool
    {
        return $value instanceof self && $value === $this;
    }
    public function isOneOf(self ...$types) : bool
    {
        return in_array($this, $types, \true);
    }
    public function filterFlag() : int
    {
        return match ($this) {
            self::Bool, self::True, self::False => FILTER_VALIDATE_BOOL,
            self::Int => FILTER_VALIDATE_INT,
            self::Float => FILTER_VALIDATE_FLOAT,
            default => FILTER_UNSAFE_RAW,
        };
    }
    public static function resolve(ReflectionProperty|ReflectionParameter $reflectionProperty, array $arguments = []) : \MenuManager\Vendor\League\Csv\Serializer\TypeCasting
    {
        try {
            $cast = match (self::tryFromAccessor($reflectionProperty)) {
                self::Mixed, self::Null, self::String => new \MenuManager\Vendor\League\Csv\Serializer\CastToString($reflectionProperty),
                self::Iterable, self::Array => new \MenuManager\Vendor\League\Csv\Serializer\CastToArray($reflectionProperty),
                self::False, self::True, self::Bool => new \MenuManager\Vendor\League\Csv\Serializer\CastToBool($reflectionProperty),
                self::Float => new \MenuManager\Vendor\League\Csv\Serializer\CastToFloat($reflectionProperty),
                self::Int => new \MenuManager\Vendor\League\Csv\Serializer\CastToInt($reflectionProperty),
                self::Date => new \MenuManager\Vendor\League\Csv\Serializer\CastToDate($reflectionProperty),
                self::Enum => new \MenuManager\Vendor\League\Csv\Serializer\CastToEnum($reflectionProperty),
                null => throw \MenuManager\Vendor\League\Csv\Serializer\MappingFailed::dueToUnsupportedType($reflectionProperty),
            };
            $cast->setOptions(...$arguments);
            return $cast;
        } catch (\MenuManager\Vendor\League\Csv\Serializer\MappingFailed $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw \MenuManager\Vendor\League\Csv\Serializer\MappingFailed::dueToInvalidCastingArguments($exception);
        }
    }
    /**
     * @return list<array{0:Type, 1: ReflectionNamedType}>
     */
    public static function list(ReflectionParameter|ReflectionProperty $reflectionProperty) : array
    {
        $reflectionType = $reflectionProperty->getType() ?? throw \MenuManager\Vendor\League\Csv\Serializer\MappingFailed::dueToUnsupportedType($reflectionProperty);
        $foundTypes = static function (array $res, ReflectionType $reflectionType) {
            if (!$reflectionType instanceof ReflectionNamedType) {
                return $res;
            }
            $type = self::tryFromName($reflectionType->getName());
            if (null !== $type) {
                $res[] = [$type, $reflectionType];
            }
            return $res;
        };
        return match (\true) {
            $reflectionType instanceof ReflectionNamedType => $foundTypes([], $reflectionType),
            $reflectionType instanceof ReflectionUnionType => \array_reduce($reflectionType->getTypes(), $foundTypes, []),
            default => [],
        };
    }
    public static function tryFromName(string $propertyType) : ?self
    {
        $interfaceExists = \interface_exists($propertyType);
        return match (\true) {
            enum_exists($propertyType), $interfaceExists && (new ReflectionClass($propertyType))->implementsInterface(UnitEnum::class) => self::Enum,
            $interfaceExists && (new ReflectionClass($propertyType))->implementsInterface(DateTimeInterface::class), class_exists($propertyType) && (new ReflectionClass($propertyType))->implementsInterface(DateTimeInterface::class) => self::Date,
            default => self::tryFrom($propertyType),
        };
    }
    public static function tryFromAccessor(ReflectionProperty|ReflectionParameter $reflectionProperty) : ?self
    {
        $type = $reflectionProperty->getType();
        if (null === $type) {
            return \MenuManager\Vendor\League\Csv\Serializer\Type::Mixed;
        }
        if ($type instanceof ReflectionNamedType) {
            return self::tryFromName($type->getName());
        }
        if (!$type instanceof ReflectionUnionType) {
            return null;
        }
        foreach ($type->getTypes() as $innerType) {
            if (!$innerType instanceof ReflectionNamedType) {
                continue;
            }
            $result = self::tryFromName($innerType->getName());
            if ($result instanceof self) {
                return $result;
            }
        }
        return null;
    }
}
