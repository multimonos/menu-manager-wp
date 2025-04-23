<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Composer\Pcre\PHPStan;

use MenuManager\Vendor\Composer\Pcre\Preg;
use MenuManager\Vendor\Composer\Pcre\Regex;
use MenuManager\Vendor\PhpParser\Node\Expr\StaticCall;
use MenuManager\Vendor\PHPStan\Analyser\Scope;
use MenuManager\Vendor\PHPStan\Reflection\MethodReflection;
use MenuManager\Vendor\PHPStan\Reflection\Native\NativeParameterReflection;
use MenuManager\Vendor\PHPStan\Reflection\ParameterReflection;
use MenuManager\Vendor\PHPStan\TrinaryLogic;
use MenuManager\Vendor\PHPStan\Type\ClosureType;
use MenuManager\Vendor\PHPStan\Type\Constant\ConstantArrayType;
use MenuManager\Vendor\PHPStan\Type\Php\RegexArrayShapeMatcher;
use MenuManager\Vendor\PHPStan\Type\StaticMethodParameterClosureTypeExtension;
use MenuManager\Vendor\PHPStan\Type\StringType;
use MenuManager\Vendor\PHPStan\Type\TypeCombinator;
use MenuManager\Vendor\PHPStan\Type\Type;
final class PregReplaceCallbackClosureTypeExtension implements StaticMethodParameterClosureTypeExtension
{
    /**
     * @var RegexArrayShapeMatcher
     */
    private $regexShapeMatcher;
    public function __construct(RegexArrayShapeMatcher $regexShapeMatcher)
    {
        $this->regexShapeMatcher = $regexShapeMatcher;
    }
    public function isStaticMethodSupported(MethodReflection $methodReflection, ParameterReflection $parameter) : bool
    {
        return \in_array($methodReflection->getDeclaringClass()->getName(), [Preg::class, Regex::class], \true) && \in_array($methodReflection->getName(), ['replaceCallback', 'replaceCallbackStrictGroups'], \true) && $parameter->getName() === 'replacement';
    }
    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, ParameterReflection $parameter, Scope $scope) : ?Type
    {
        $args = $methodCall->getArgs();
        $patternArg = $args[0] ?? null;
        $flagsArg = $args[5] ?? null;
        if ($patternArg === null) {
            return null;
        }
        $flagsType = \MenuManager\Vendor\Composer\Pcre\PHPStan\PregMatchFlags::getType($flagsArg, $scope);
        $matchesType = $this->regexShapeMatcher->matchExpr($patternArg->value, $flagsType, TrinaryLogic::createYes(), $scope);
        if ($matchesType === null) {
            return null;
        }
        if ($methodReflection->getName() === 'replaceCallbackStrictGroups' && \count($matchesType->getConstantArrays()) === 1) {
            $matchesType = $matchesType->getConstantArrays()[0];
            $matchesType = new ConstantArrayType($matchesType->getKeyTypes(), \array_map(static function (Type $valueType) : Type {
                if (\count($valueType->getConstantArrays()) === 1) {
                    $valueTypeArray = $valueType->getConstantArrays()[0];
                    return new ConstantArrayType($valueTypeArray->getKeyTypes(), \array_map(static function (Type $valueType) : Type {
                        return TypeCombinator::removeNull($valueType);
                    }, $valueTypeArray->getValueTypes()), $valueTypeArray->getNextAutoIndexes(), [], $valueTypeArray->isList());
                }
                return TypeCombinator::removeNull($valueType);
            }, $matchesType->getValueTypes()), $matchesType->getNextAutoIndexes(), [], $matchesType->isList());
        }
        return new ClosureType([new NativeParameterReflection($parameter->getName(), $parameter->isOptional(), $matchesType, $parameter->passedByReference(), $parameter->isVariadic(), $parameter->getDefaultValue())], new StringType());
    }
}
