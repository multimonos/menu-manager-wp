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

use Closure;
use MenuManager\Vendor\Deprecated;
use Iterator;
use MenuManager\Vendor\League\Csv\MapIterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Throwable;
use function array_search;
use function array_values;
use function count;
use function is_int;
final class Denormalizer
{
    private static bool $convertEmptyStringToNull = \true;
    private readonly ReflectionClass $class;
    /** @var array<ReflectionProperty> */
    private readonly array $properties;
    /** @var array<PropertySetter> */
    private readonly array $propertySetters;
    /** @var array<ReflectionMethod> */
    private readonly array $afterMappingCalls;
    private readonly ?\MenuManager\Vendor\League\Csv\Serializer\MapRecord $mapRecord;
    /**
     * @param class-string $className
     * @param array<string> $propertyNames
     *
     * @throws MappingFailed
     */
    public function __construct(string $className, array $propertyNames = [])
    {
        $this->class = $this->setClass($className);
        $this->properties = $this->class->getProperties();
        $this->mapRecord = \MenuManager\Vendor\League\Csv\Serializer\MapRecord::tryFrom($this->class);
        $this->propertySetters = $this->setPropertySetters($propertyNames);
        $this->afterMappingCalls = $this->setAfterMappingCalls();
    }
    /**
     * @deprecated since version 9.17.0
     *
     * @see MapRecord::$convertEmptyStringToNull
     * @see MapCell::$convertEmptyStringToNull
     *
     * Enables converting empty string to the null value.
     */
    #[\Deprecated(message: 'use League\\Csv\\Serializer\\MapRecord::$convertEmptyStringToNull or League\\Csv\\Serializer\\MapCell::$convertEmptyStringToNullinstead', since: 'league/csv:9.17.0')]
    public static function allowEmptyStringAsNull() : void
    {
        self::$convertEmptyStringToNull = \true;
    }
    /**
     * @deprecated since version 9.17.0
     *
     * @see MapRecord::$convertEmptyStringToNull
     * @see MapCell::$convertEmptyStringToNull
     *
     * Disables converting empty string to the null value.
     */
    #[\Deprecated(message: 'use League\\Csv\\Serializer\\MapRecord::$convertEmptyStringToNull or League\\Csv\\Serializer\\MapCell::$convertEmptyStringToNullinstead', since: 'league/csv:9.17.0')]
    public static function disallowEmptyStringAsNull() : void
    {
        self::$convertEmptyStringToNull = \false;
    }
    /**
     * Register a global type conversion callback to convert a field into a specific type.
     *
     * @throws MappingFailed
     */
    public static function registerType(string $type, Closure $callback) : void
    {
        \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::register($type, $callback);
    }
    /**
     * Unregister a global type conversion callback to convert a field into a specific type.
     *
     *
     */
    public static function unregisterType(string $type) : bool
    {
        return \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::unregisterType($type);
    }
    public static function unregisterAllTypes() : void
    {
        \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::unregisterTypes();
    }
    /**
     * Register a callback to convert a field into a specific type.
     *
     * @throws MappingFailed
     */
    public static function registerAlias(string $alias, string $type, Closure $callback) : void
    {
        \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::register($type, $callback, $alias);
    }
    public static function unregisterAlias(string $alias) : bool
    {
        return \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::unregisterAlias($alias);
    }
    public static function unregisterAllAliases() : void
    {
        \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::unregisterAliases();
    }
    public static function unregisterAll() : void
    {
        \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::unregisterAll();
    }
    /**
     * @return array<string>
     */
    public static function types() : array
    {
        $default = [...\array_column(\MenuManager\Vendor\League\Csv\Serializer\Type::cases(), 'value'), ...\MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::types()];
        return array_values(\array_unique($default));
    }
    /**
     * @return array<string, string>
     */
    public static function aliases() : array
    {
        return \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::aliases();
    }
    public static function supportsAlias(string $alias) : bool
    {
        return \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::supportsAlias($alias);
    }
    /**
     * @param class-string $className
     * @param array<array-key, mixed> $record
     *
     * @throws DenormalizationFailed
     * @throws MappingFailed
     * @throws ReflectionException
     * @throws TypeCastingFailed
     */
    public static function assign(string $className, array $record) : object
    {
        return (new self($className, \array_keys($record)))->denormalize($record);
    }
    /**
     * @param class-string $className
     * @param array<string> $propertyNames
     *
     * @throws MappingFailed
     * @throws TypeCastingFailed
     */
    public static function assignAll(string $className, iterable $records, array $propertyNames = []) : Iterator
    {
        return (new self($className, $propertyNames))->denormalizeAll($records);
    }
    public function denormalizeAll(iterable $records) : Iterator
    {
        return MapIterator::fromIterable($records, $this->denormalize(...));
    }
    /**
     * @throws DenormalizationFailed
     * @throws ReflectionException
     * @throws TypeCastingFailed
     */
    public function denormalize(array $record) : object
    {
        $object = $this->class->newInstanceWithoutConstructor();
        $values = array_values($record);
        foreach ($this->propertySetters as $propertySetter) {
            $propertySetter($object, $values);
        }
        foreach ($this->afterMappingCalls as $callback) {
            $callback->invoke($object);
        }
        foreach ($this->properties as $property) {
            $property->isInitialized($object) || throw \MenuManager\Vendor\League\Csv\Serializer\DenormalizationFailed::dueToUninitializedProperty($property);
        }
        return $object;
    }
    /**
     * @param class-string $className
     *
     * @throws MappingFailed
     */
    private function setClass(string $className) : ReflectionClass
    {
        \class_exists($className) || throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('The class `' . $className . '` can not be denormalized; The class does not exist or could not be found.');
        $class = new ReflectionClass($className);
        if ($class->isInternal() && $class->isFinal()) {
            throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('The class `' . $className . '` can not be denormalized; PHP internal class marked as final can not be instantiated without using the constructor.');
        }
        return $class;
    }
    /**
     * @param array<string> $propertyNames
     *
     * @throws MappingFailed
     *
     * @return array<PropertySetter>
     */
    private function setPropertySetters(array $propertyNames) : array
    {
        $propertySetters = [];
        $methodNames = \array_map(fn(string $propertyName) => 'set' . \ucfirst($propertyName), $propertyNames);
        foreach ([...$this->properties, ...$this->class->getMethods()] as $accessor) {
            $attributes = $accessor->getAttributes(\MenuManager\Vendor\League\Csv\Serializer\MapCell::class, ReflectionAttribute::IS_INSTANCEOF);
            $propertySetter = match (count($attributes)) {
                0 => $this->autoDiscoverPropertySetter($accessor, $propertyNames, $methodNames),
                1 => $this->findPropertySetter($attributes[0]->newInstance(), $accessor, $propertyNames),
                default => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('Using more than one `' . \MenuManager\Vendor\League\Csv\Serializer\MapCell::class . '` attribute on a class property or method is not supported.'),
            };
            if (null !== $propertySetter) {
                $propertySetters[] = $propertySetter;
            }
        }
        return match ([]) {
            $propertySetters => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('No property or method from `' . $this->class->getName() . '` could be used for denormalization.'),
            default => $propertySetters,
        };
    }
    /**
     * @return array<ReflectionMethod>
     */
    private function setAfterMappingCalls() : array
    {
        return $this->mapRecord?->afterMappingMethods($this->class) ?? \MenuManager\Vendor\League\Csv\Serializer\AfterMapping::from($this->class)?->mapRecord->afterMappingMethods($this->class) ?? [];
    }
    /**
     * @param array<string> $propertyNames
     * @param array<?string> $methodNames
     *
     * @throws MappingFailed
     */
    private function autoDiscoverPropertySetter(ReflectionMethod|ReflectionProperty $accessor, array $propertyNames, array $methodNames) : ?\MenuManager\Vendor\League\Csv\Serializer\PropertySetter
    {
        if ($accessor->isStatic() || !$accessor->isPublic()) {
            return null;
        }
        if ($accessor instanceof ReflectionMethod) {
            if ($accessor->isConstructor()) {
                return null;
            }
            if ([] === $accessor->getParameters()) {
                return null;
            }
            if (1 < $accessor->getNumberOfRequiredParameters()) {
                return null;
            }
        }
        /** @var int|false $offset */
        /** @var ReflectionParameter|ReflectionProperty $reflectionProperty */
        [$offset, $reflectionProperty] = match (\true) {
            $accessor instanceof ReflectionMethod => [array_search($accessor->getName(), $methodNames, \true), $accessor->getParameters()[0]],
            $accessor instanceof ReflectionProperty => [array_search($accessor->getName(), $propertyNames, \true), $accessor],
        };
        return match (\true) {
            \false === $offset, null === $reflectionProperty->getType() => null,
            default => new \MenuManager\Vendor\League\Csv\Serializer\PropertySetter($accessor, $offset, $this->resolveTypeCasting($reflectionProperty), $this->mapRecord?->convertEmptyStringToNull ?? self::$convertEmptyStringToNull, $this->mapRecord?->trimFieldValueBeforeCasting ?? \false),
        };
    }
    /**
     * @param array<string> $propertyNames
     *
     * @throws MappingFailed
     */
    private function findPropertySetter(\MenuManager\Vendor\League\Csv\Serializer\MapCell $mapCell, ReflectionMethod|ReflectionProperty $accessor, array $propertyNames) : ?\MenuManager\Vendor\League\Csv\Serializer\PropertySetter
    {
        if ($mapCell->ignore) {
            return null;
        }
        $typeCaster = $this->resolveTypeCaster($mapCell, $accessor);
        $offset = $mapCell->column ?? match (\true) {
            $accessor instanceof ReflectionMethod => $this->getMethodFirstArgument($accessor)->getName(),
            $accessor instanceof ReflectionProperty => $accessor->getName(),
        };
        if (!is_int($offset)) {
            if ([] === $propertyNames) {
                throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('offset as string are only supported if the property names list is not empty.');
            }
            /** @var int<0, max>|false $index */
            $index = array_search($offset, $propertyNames, \true);
            if (\false === $index) {
                throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('The `' . $offset . '` property could not be found in the property names list; Please verify your property names list.');
            }
            $offset = $index;
        }
        $reflectionProperty = match (\true) {
            $accessor instanceof ReflectionMethod => $accessor->getParameters()[0],
            $accessor instanceof ReflectionProperty => $accessor,
        };
        $convertEmptyStringToNull = $mapCell->convertEmptyStringToNull ?? $this->mapRecord?->convertEmptyStringToNull ?? self::$convertEmptyStringToNull;
        $trimFieldValueBeforeCasting = $mapCell->trimFieldValueBeforeCasting ?? $this->mapRecord?->trimFieldValueBeforeCasting ?? \false;
        return match (\true) {
            0 > $offset => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('offset integer position can only be positive or equals to 0; received `' . $offset . '`'),
            [] !== $propertyNames && $offset > count($propertyNames) - 1 => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('offset integer position can not exceed property names count.'),
            null === $typeCaster => new \MenuManager\Vendor\League\Csv\Serializer\PropertySetter($accessor, $offset, $this->resolveTypeCasting($reflectionProperty, $mapCell->options), $convertEmptyStringToNull, $trimFieldValueBeforeCasting),
            default => new \MenuManager\Vendor\League\Csv\Serializer\PropertySetter($accessor, $offset, $this->getTypeCasting($typeCaster, $reflectionProperty, $mapCell->options), $convertEmptyStringToNull, $trimFieldValueBeforeCasting),
        };
    }
    /**
     * @throws MappingFailed
     */
    private function getMethodFirstArgument(ReflectionMethod $reflectionMethod) : ReflectionParameter
    {
        $arguments = $reflectionMethod->getParameters();
        return match (\true) {
            [] === $arguments => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('The method `' . $reflectionMethod->getDeclaringClass()->getName() . '::' . $reflectionMethod->getName() . '` does not use parameters.'),
            1 < $reflectionMethod->getNumberOfRequiredParameters() => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('The method `' . $reflectionMethod->getDeclaringClass()->getName() . '::' . $reflectionMethod->getName() . '` has too many required parameters.'),
            default => $arguments[0],
        };
    }
    /**
     * @throws MappingFailed
     */
    private function getTypeCasting(string $typeCaster, ReflectionProperty|ReflectionParameter $reflectionProperty, array $options) : \MenuManager\Vendor\League\Csv\Serializer\TypeCasting
    {
        try {
            /** @var TypeCasting $cast */
            $cast = match (\str_starts_with($typeCaster, \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::class . '@')) {
                \true => new \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting($reflectionProperty, \substr($typeCaster, \strlen(\MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::class))),
                \false => new $typeCaster($reflectionProperty),
            };
            $cast->setOptions(...$options);
            return $cast;
        } catch (\MenuManager\Vendor\League\Csv\Serializer\MappingFailed $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw \MenuManager\Vendor\League\Csv\Serializer\MappingFailed::dueToInvalidCastingArguments($exception);
        }
    }
    /**
     * @throws MappingFailed
     */
    private function resolveTypeCasting(ReflectionProperty|ReflectionParameter $reflectionProperty, array $options = []) : \MenuManager\Vendor\League\Csv\Serializer\TypeCasting
    {
        $castResolver = function (ReflectionProperty|ReflectionParameter $reflectionProperty, $options) : \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting {
            $cast = new \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting($reflectionProperty);
            $cast->setOptions(...$options);
            return $cast;
        };
        try {
            return match (\true) {
                \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::supports($reflectionProperty) => $castResolver($reflectionProperty, $options),
                default => \MenuManager\Vendor\League\Csv\Serializer\Type::resolve($reflectionProperty, $options),
            };
        } catch (\MenuManager\Vendor\League\Csv\Serializer\MappingFailed $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw \MenuManager\Vendor\League\Csv\Serializer\MappingFailed::dueToInvalidCastingArguments($exception);
        }
    }
    public function resolveTypeCaster(\MenuManager\Vendor\League\Csv\Serializer\MapCell $mapCell, ReflectionMethod|ReflectionProperty $accessor) : ?string
    {
        /** @var ?class-string<TypeCasting> $typeCaster */
        $typeCaster = $mapCell->cast;
        if (null === $typeCaster) {
            return null;
        }
        if (\class_exists($typeCaster)) {
            if (!(new ReflectionClass($typeCaster))->implementsInterface(\MenuManager\Vendor\League\Csv\Serializer\TypeCasting::class)) {
                throw \MenuManager\Vendor\League\Csv\Serializer\MappingFailed::dueToInvalidTypeCastingClass($typeCaster);
            }
            return $typeCaster;
        }
        if ($accessor instanceof ReflectionMethod) {
            $accessor = $accessor->getParameters()[0];
        }
        if (!\MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::supports($accessor, $typeCaster)) {
            throw \MenuManager\Vendor\League\Csv\Serializer\MappingFailed::dueToInvalidTypeCastingClass($typeCaster);
        }
        return \MenuManager\Vendor\League\Csv\Serializer\CallbackCasting::class . $typeCaster;
    }
}
