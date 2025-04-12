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

use Attribute;
use MenuManager\Vendor\Deprecated;
use ReflectionAttribute;
use ReflectionClass;
/**
 * @deprecated since version 9.17.0
 *
 * @see MapRecord
 */
#[\Attribute(Attribute::TARGET_CLASS)]
final class AfterMapping
{
    public readonly \MenuManager\Vendor\League\Csv\Serializer\MapRecord $mapRecord;
    public readonly array $methods;
    #[\Deprecated(message: 'use League\\Csv\\Serializer\\MapRecord instead', since: 'league/csv:9.17.0')]
    public function __construct(string ...$methods)
    {
        $this->mapRecord = new \MenuManager\Vendor\League\Csv\Serializer\MapRecord($methods);
        $this->methods = $this->mapRecord->afterMapping;
    }
    public static function from(ReflectionClass $class) : ?self
    {
        $attributes = $class->getAttributes(self::class, ReflectionAttribute::IS_INSTANCEOF);
        $nbAttributes = \count($attributes);
        return match (\true) {
            0 === $nbAttributes => null,
            1 < $nbAttributes => throw new \MenuManager\Vendor\League\Csv\Serializer\MappingFailed('Using more than one `' . self::class . '` attribute on a class property or method is not supported.'),
            default => $attributes[0]->newInstance(),
        };
    }
}
