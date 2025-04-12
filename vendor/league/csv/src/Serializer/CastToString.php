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
/**
 * @implements TypeCasting<?string>
 */
final class CastToString implements \MenuManager\Vendor\League\Csv\Serializer\TypeCasting
{
    private readonly bool $isNullable;
    private readonly \MenuManager\Vendor\League\Csv\Serializer\Type $type;
    private ?string $default = null;
    private readonly \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo $variableName;
    public function __construct(ReflectionProperty|ReflectionParameter $reflectionProperty)
    {
        [$this->type, $this->isNullable] = $this->init($reflectionProperty);
        $this->variableName = \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo::fromAccessor($reflectionProperty);
    }
    public function info() : \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo
    {
        return $this->variableName;
    }
    public function setOptions(?string $default = null, bool $emptyStringAsNull = \false) : void
    {
        $this->default = $default;
    }
    /**
     * @throws TypeCastingFailed
     */
    public function toVariable(mixed $value) : ?string
    {
        $returnedValue = match (\true) {
            \is_string($value) => $value,
            $this->isNullable => $this->default,
            default => throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToNotNullableType($this->type->value, info: $this->variableName),
        };
        return match (\true) {
            \MenuManager\Vendor\League\Csv\Serializer\Type::Null->equals($this->type) && null !== $returnedValue => throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToInvalidValue(match (\true) {
                null === $value => 'null',
                '' === $value => 'empty string',
                default => $value,
            }, $this->type->value, info: $this->variableName),
            default => $returnedValue,
        };
    }
    /**
     * @return array{0:Type, 1:bool}
     */
    private function init(ReflectionProperty|ReflectionParameter $reflectionProperty) : array
    {
        if (null === $reflectionProperty->getType()) {
            return [\MenuManager\Vendor\League\Csv\Serializer\Type::Mixed, \true];
        }
        $type = null;
        $isNullable = \false;
        foreach (\MenuManager\Vendor\League\Csv\Serializer\Type::list($reflectionProperty) as $found) {
            if (!$isNullable && $found[1]->allowsNull()) {
                $isNullable = \true;
            }
            if (null === $type && $found[0]->isOneOf(\MenuManager\Vendor\League\Csv\Serializer\Type::String, \MenuManager\Vendor\League\Csv\Serializer\Type::Mixed, \MenuManager\Vendor\League\Csv\Serializer\Type::Null)) {
                $type = $found;
            }
        }
        null !== $type || throw throw \MenuManager\Vendor\League\Csv\Serializer\MappingFailed::dueToTypeCastingUnsupportedType($reflectionProperty, $this, 'string', 'mixed', 'null');
        return [$type[0], $isNullable];
    }
}
