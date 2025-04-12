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

use ReflectionParameter;
use ReflectionProperty;
use function filter_var;
/**
 * @implements TypeCasting<?int>
 */
final class CastToInt implements \MenuManager\Vendor\League\Csv\Serializer\TypeCasting
{
    private readonly bool $isNullable;
    private ?int $default = null;
    private readonly \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo $info;
    public function __construct(ReflectionProperty|ReflectionParameter $reflectionProperty)
    {
        $this->isNullable = $this->init($reflectionProperty);
        $this->info = \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo::fromAccessor($reflectionProperty);
    }
    public function info() : \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo
    {
        return $this->info;
    }
    public function setOptions(?int $default = null, bool $emptyStringAsNull = \false) : void
    {
        $this->default = $default;
    }
    /**
     * @throws TypeCastingFailed
     */
    public function toVariable(mixed $value) : ?int
    {
        if (null === $value) {
            return match ($this->isNullable) {
                \true => $this->default,
                \false => throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToNotNullableType('integer', info: $this->info),
            };
        }
        \is_scalar($value) || throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToInvalidValue($value, \MenuManager\Vendor\League\Csv\Serializer\Type::Int->value, info: $this->info);
        $int = filter_var($value, \MenuManager\Vendor\League\Csv\Serializer\Type::Int->filterFlag());
        return match ($int) {
            \false => throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToInvalidValue($value, \MenuManager\Vendor\League\Csv\Serializer\Type::Int->value, info: $this->info),
            default => $int,
        };
    }
    private function init(ReflectionProperty|ReflectionParameter $reflectionProperty) : bool
    {
        if (null === $reflectionProperty->getType()) {
            return \true;
        }
        $type = null;
        $isNullable = \false;
        foreach (\MenuManager\Vendor\League\Csv\Serializer\Type::list($reflectionProperty) as $found) {
            if (!$isNullable && $found[1]->allowsNull()) {
                $isNullable = \true;
            }
            if (null === $type && $found[0]->isOneOf(\MenuManager\Vendor\League\Csv\Serializer\Type::Mixed, \MenuManager\Vendor\League\Csv\Serializer\Type::Int, \MenuManager\Vendor\League\Csv\Serializer\Type::Float)) {
                $type = $found;
            }
        }
        null !== $type || throw throw \MenuManager\Vendor\League\Csv\Serializer\MappingFailed::dueToTypeCastingUnsupportedType($reflectionProperty, $this, 'int', 'float', 'null', 'mixed');
        return $isNullable;
    }
}
