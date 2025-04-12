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

use JsonException;
use MenuManager\Vendor\League\Csv\Exception;
use MenuManager\Vendor\League\Csv\Reader;
use ReflectionParameter;
use ReflectionProperty;
use function array_map;
use function explode;
use function filter_var;
use function is_array;
use function json_decode;
use function strlen;
use const FILTER_REQUIRE_ARRAY;
use const JSON_THROW_ON_ERROR;
/**
 * @implements TypeCasting<array|null>
 */
final class CastToArray implements \MenuManager\Vendor\League\Csv\Serializer\TypeCasting
{
    private readonly \MenuManager\Vendor\League\Csv\Serializer\Type $type;
    private readonly bool $isNullable;
    private \MenuManager\Vendor\League\Csv\Serializer\ArrayShape $shape;
    private int $filterFlag;
    /** @var non-empty-string */
    private string $separator = ',';
    private string $delimiter = '';
    private string $enclosure = '"';
    /** @var int<1, max> $depth */
    private int $depth = 512;
    private int $flags = 0;
    private ?array $default = null;
    private bool $trimElementValueBeforeCasting = \false;
    private ?int $headerOffset = null;
    private readonly \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo $info;
    /**
     * @throws MappingFailed
     */
    public function __construct(ReflectionProperty|ReflectionParameter $reflectionProperty)
    {
        [$this->type, $this->isNullable] = $this->init($reflectionProperty);
        $this->shape = \MenuManager\Vendor\League\Csv\Serializer\ArrayShape::List;
        $this->filterFlag = \MenuManager\Vendor\League\Csv\Serializer\Type::String->filterFlag();
        $this->info = \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo::fromAccessor($reflectionProperty);
    }
    public function info() : \MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo
    {
        return $this->info;
    }
    /**
     * @param non-empty-string $delimiter
     * @param non-empty-string $separator
     * @param int<1, max> $depth
     *
     * @throws MappingFailed
     */
    public function setOptions(?array $default = null, \MenuManager\Vendor\League\Csv\Serializer\ArrayShape|string $shape = \MenuManager\Vendor\League\Csv\Serializer\ArrayShape::List, string $separator = ',', string $delimiter = ',', string $enclosure = '"', int $depth = 512, int $flags = 0, \MenuManager\Vendor\League\Csv\Serializer\Type|string $type = \MenuManager\Vendor\League\Csv\Serializer\Type::String, bool $trimElementValueBeforeCasting = \false, ?int $headerOffset = null) : void
    {
        if (!$shape instanceof \MenuManager\Vendor\League\Csv\Serializer\ArrayShape) {
            $shape = \MenuManager\Vendor\League\Csv\Serializer\ArrayShape::tryFrom($shape) ?? throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('Unable to resolve the array shape; Verify your options arguments.');
        }
        if (!$type instanceof \MenuManager\Vendor\League\Csv\Serializer\Type) {
            $type = \MenuManager\Vendor\League\Csv\Serializer\Type::tryFrom($type) ?? throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('Unable to resolve the array value type; Verify your options arguments.');
        }
        $this->shape = $shape;
        $this->depth = $depth;
        $this->separator = $separator;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->flags = $flags;
        $this->default = $default;
        $this->filterFlag = match (\true) {
            1 > $this->depth && $this->shape->equals(\MenuManager\Vendor\League\Csv\Serializer\ArrayShape::Json) => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('the json depth can not be less than 1.'),
            1 > strlen($this->separator) && $this->shape->equals(\MenuManager\Vendor\League\Csv\Serializer\ArrayShape::List) => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('expects separator to be a non-empty string for list conversion; empty string given.'),
            1 !== strlen($this->delimiter) && $this->shape->equals(\MenuManager\Vendor\League\Csv\Serializer\ArrayShape::Csv) => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('expects delimiter to be a single character for CSV conversion; `' . $this->delimiter . '` given.'),
            1 !== strlen($this->enclosure) && $this->shape->equals(\MenuManager\Vendor\League\Csv\Serializer\ArrayShape::Csv) => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('expects enclosure to be a single character; `' . $this->enclosure . '` given.'),
            default => $this->resolveFilterFlag($type),
        };
        $this->trimElementValueBeforeCasting = $trimElementValueBeforeCasting;
        $this->headerOffset = $headerOffset;
    }
    public function toVariable(mixed $value) : ?array
    {
        if (null === $value) {
            return match (\true) {
                $this->isNullable, \MenuManager\Vendor\League\Csv\Serializer\Type::Mixed->equals($this->type) => $this->default,
                default => throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToNotNullableType($this->type->value, info: $this->info),
            };
        }
        if ('' === $value) {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        if (!\is_string($value)) {
            throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToInvalidValue($value, $this->type->value, info: $this->info);
        }
        if ($this->shape->equals(\MenuManager\Vendor\League\Csv\Serializer\ArrayShape::Json)) {
            try {
                $data = json_decode($value, \true, $this->depth, $this->flags | JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToInvalidValue($value, $this->type->value, $exception, $this->info);
            }
            if (!is_array($data)) {
                throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToInvalidValue($value, $this->type->value, info: $this->info);
            }
            return $data;
        }
        if ($this->shape->equals(\MenuManager\Vendor\League\Csv\Serializer\ArrayShape::Csv)) {
            try {
                $data = Reader::createFromString($value);
                $data->setDelimiter($this->delimiter);
                $data->setEnclosure($this->enclosure);
                $data->setEscape('');
                $data->setHeaderOffset($this->headerOffset);
                if ($this->trimElementValueBeforeCasting) {
                    $data->addFormatter($this->trimString(...));
                }
                $data->addFormatter($this->filterElement(...));
                return [...$data];
            } catch (Exception $exception) {
                throw \MenuManager\Vendor\League\Csv\Serializer\TypeCastingFailed::dueToInvalidValue($value, $this->type->value, $exception, $this->info);
            }
        }
        $data = explode($this->separator, $value);
        return $this->filterElement(match (\true) {
            $this->trimElementValueBeforeCasting => $this->trimString($data),
            default => $data,
        });
    }
    private function trimString(array $record) : array
    {
        return array_map(fn(mixed $value): mixed => \is_string($value) ? \trim($value) : $value, $record);
    }
    private function filterElement(array $record) : array
    {
        return filter_var($record, $this->filterFlag, FILTER_REQUIRE_ARRAY);
    }
    /**
     * @throws MappingFailed if the type is not supported
     */
    private function resolveFilterFlag(?\MenuManager\Vendor\League\Csv\Serializer\Type $type) : int
    {
        return match (\true) {
            $this->shape->equals(\MenuManager\Vendor\League\Csv\Serializer\ArrayShape::Json) => \MenuManager\Vendor\League\Csv\Serializer\Type::String->filterFlag(),
            $type instanceof \MenuManager\Vendor\League\Csv\Serializer\Type && $type->isOneOf(\MenuManager\Vendor\League\Csv\Serializer\Type::Bool, \MenuManager\Vendor\League\Csv\Serializer\Type::True, \MenuManager\Vendor\League\Csv\Serializer\Type::False, \MenuManager\Vendor\League\Csv\Serializer\Type::String, \MenuManager\Vendor\League\Csv\Serializer\Type::Float, \MenuManager\Vendor\League\Csv\Serializer\Type::Int) => $type->filterFlag(),
            default => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('Only scalar type are supported for `array` value casting.'),
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
            if (null === $type && $found[0]->isOneOf(\MenuManager\Vendor\League\Csv\Serializer\Type::Mixed, \MenuManager\Vendor\League\Csv\Serializer\Type::Array, \MenuManager\Vendor\League\Csv\Serializer\Type::Iterable)) {
                $type = $found;
            }
        }
        if (null === $type) {
            throw \MenuManager\Vendor\League\Csv\Serializer\MappingFailed::dueToTypeCastingUnsupportedType($reflectionProperty, $this, 'array', 'iterable', 'mixed');
        }
        return [$type[0], $isNullable];
    }
}
