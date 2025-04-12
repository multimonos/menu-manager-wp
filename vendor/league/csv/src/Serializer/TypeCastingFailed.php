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

use RuntimeException;
use Throwable;
use function is_int;
final class TypeCastingFailed extends RuntimeException implements \MenuManager\Vendor\League\Csv\Serializer\SerializationFailed
{
    public readonly ?\MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo $info;
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null, ?\MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo $info = null)
    {
        parent::__construct(self::format($message, $info), $code, $previous);
        $this->info = $info;
    }
    private static function format(string $message, ?\MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo $info = null) : string
    {
        if (null === $info) {
            return $message;
        }
        $className = $info->targetClassName;
        if (null !== $className) {
            $className .= '::';
        }
        $target = $info->targetName;
        $target = \MenuManager\Vendor\League\Csv\Serializer\TypeCastingTargetType::MethodFirstArgument === $info->targetType ? 'the first argument `' . $target . '` of the method `' . $className . $info->targetMethodName . '()`' : 'the property `' . $className . $target . '`';
        $source = $info->source;
        $source = is_int($source) ? "the record field offset `{$source}`" : "the record field `{$source}`";
        return "Casting {$target} using {$source} failed; {$message}";
    }
    public static function dueToNotNullableType(string $type, ?Throwable $exception = null, ?\MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo $info = null) : self
    {
        return new self('The `null` value can not be cast to a `' . $type . '`; the property type is not nullable.', 0, $exception, $info);
    }
    public static function dueToInvalidValue(mixed $value, string $type, ?Throwable $previous = null, ?\MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo $info = null) : self
    {
        if (!\is_scalar($value)) {
            $value = \gettype($value);
        }
        return new self('Unable to cast the given data `' . $value . '` to a `' . $type . '`.', 0, $previous, $info);
    }
    public static function dueToUndefinedValue(string|int $offset, ?\MenuManager\Vendor\League\Csv\Serializer\TypeCastingInfo $info = null) : self
    {
        return new self('Unable to cast the record value; Missing value was for offset `' . $offset . '`.', 0, info: $info);
    }
}
