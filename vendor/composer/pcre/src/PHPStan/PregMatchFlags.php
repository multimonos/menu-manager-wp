<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Composer\Pcre\PHPStan;

use MenuManager\Vendor\PHPStan\Analyser\Scope;
use MenuManager\Vendor\PHPStan\Type\ArrayType;
use MenuManager\Vendor\PHPStan\Type\Constant\ConstantArrayType;
use MenuManager\Vendor\PHPStan\Type\Constant\ConstantIntegerType;
use MenuManager\Vendor\PHPStan\Type\IntersectionType;
use MenuManager\Vendor\PHPStan\Type\TypeCombinator;
use MenuManager\Vendor\PHPStan\Type\Type;
use MenuManager\Vendor\PhpParser\Node\Arg;
use MenuManager\Vendor\PHPStan\Type\Php\RegexArrayShapeMatcher;
use MenuManager\Vendor\PHPStan\Type\TypeTraverser;
use MenuManager\Vendor\PHPStan\Type\UnionType;
final class PregMatchFlags
{
    public static function getType(?Arg $flagsArg, Scope $scope) : ?Type
    {
        if ($flagsArg === null) {
            return new ConstantIntegerType(\PREG_UNMATCHED_AS_NULL);
        }
        $flagsType = $scope->getType($flagsArg->value);
        $constantScalars = $flagsType->getConstantScalarValues();
        if ($constantScalars === []) {
            return null;
        }
        $internalFlagsTypes = [];
        foreach ($flagsType->getConstantScalarValues() as $constantScalarValue) {
            if (!\is_int($constantScalarValue)) {
                return null;
            }
            $internalFlagsTypes[] = new ConstantIntegerType($constantScalarValue | \PREG_UNMATCHED_AS_NULL);
        }
        return TypeCombinator::union(...$internalFlagsTypes);
    }
    public static function removeNullFromMatches(Type $matchesType) : Type
    {
        return TypeTraverser::map($matchesType, static function (Type $type, callable $traverse) : Type {
            if ($type instanceof UnionType || $type instanceof IntersectionType) {
                return $traverse($type);
            }
            if ($type instanceof ConstantArrayType) {
                return new ConstantArrayType($type->getKeyTypes(), \array_map(static function (Type $valueType) use($traverse) : Type {
                    return $traverse($valueType);
                }, $type->getValueTypes()), $type->getNextAutoIndexes(), [], $type->isList());
            }
            if ($type instanceof ArrayType) {
                return new ArrayType($type->getKeyType(), $traverse($type->getItemType()));
            }
            return TypeCombinator::removeNull($type);
        });
    }
}
