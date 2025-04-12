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
 * @implements TypeCasting<?bool>
 */
final class CastToBool implements \MenuManager\Vendor\League\Csv\Serializer\TypeCasting
{
    private readonly bool $isNullable;
    private readonly \MenuManager\Vendor\League\Csv\Serializer\Type $type;
    private readonly \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo $info;
    private ?bool $default = null;
    public function __construct(ReflectionProperty|ReflectionParameter $reflectionProperty)
    {
        [$this->type, $this->isNullable] = $this->init($reflectionProperty);
        $this->info = \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo::fromAccessor($reflectionProperty);
    }
    public function setOptions(?bool $default = null, bool $emptyStringAsNull = \false) : void
    {
        $this->default = $default;
    }
    public function info() : \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo
    {
        return $this->info;
    }
    /**
     * @throws TypeCastingFailed
     */
    public function toVariable(mixed $value) : ?bool
    {
        $returnValue = match (\true) {
            \is_bool($value) => $value,
            null !== $value => filter_var($value, \MenuManager\Vendor\League\Csv\Serializer\Type::Bool->filterFlag()),
            $this->isNullable => $this->default,
            default => throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToNotNullableType('boolean', info: $this->info),
        };
        return match (\true) {
            \MenuManager\Vendor\League\Csv\Serializer\Type::True->equals($this->type) && \true !== $returnValue && !$this->isNullable, \MenuManager\Vendor\League\Csv\Serializer\Type::False->equals($this->type) && \false !== $returnValue && !$this->isNullable => throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToInvalidValue(match (\true) {
                null === $value => 'null',
                '' === $value => 'empty string',
                default => $value,
            }, $this->type->value, info: $this->info),
            default => $returnValue,
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
            if (null === $type && $found[0]->isOneOf(\MenuManager\Vendor\League\Csv\Serializer\Type::Mixed, \MenuManager\Vendor\League\Csv\Serializer\Type::Bool, \MenuManager\Vendor\League\Csv\Serializer\Type::True, \MenuManager\Vendor\League\Csv\Serializer\Type::False)) {
                $type = $found;
            }
        }
        if (null === $type) {
            throw \MenuManager\Vendor\League\Csv\Serializer\MappingFailed::dueToTypeCastingUnsupportedType($reflectionProperty, $this, 'bool', 'mixed');
        }
        return [$type[0], $isNullable];
    }
}
