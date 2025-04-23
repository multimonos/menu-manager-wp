<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Composer\Pcre\PHPStan;

use MenuManager\Vendor\Composer\Pcre\Preg;
use MenuManager\Vendor\PhpParser\Node\Expr\StaticCall;
use MenuManager\Vendor\PHPStan\Analyser\Scope;
use MenuManager\Vendor\PHPStan\Reflection\MethodReflection;
use MenuManager\Vendor\PHPStan\Reflection\ParameterReflection;
use MenuManager\Vendor\PHPStan\TrinaryLogic;
use MenuManager\Vendor\PHPStan\Type\Php\RegexArrayShapeMatcher;
use MenuManager\Vendor\PHPStan\Type\StaticMethodParameterOutTypeExtension;
use MenuManager\Vendor\PHPStan\Type\Type;
final class PregMatchParameterOutTypeExtension implements StaticMethodParameterOutTypeExtension
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
        return $methodReflection->getDeclaringClass()->getName() === Preg::class && \in_array($methodReflection->getName(), ['match', 'isMatch', 'matchStrictGroups', 'isMatchStrictGroups', 'matchAll', 'isMatchAll', 'matchAllStrictGroups', 'isMatchAllStrictGroups'], \true) && $parameter->getName() === 'matches';
    }
    public function getParameterOutTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, ParameterReflection $parameter, Scope $scope) : ?Type
    {
        $args = $methodCall->getArgs();
        $patternArg = $args[0] ?? null;
        $matchesArg = $args[2] ?? null;
        $flagsArg = $args[3] ?? null;
        if ($patternArg === null || $matchesArg === null) {
            return null;
        }
        $flagsType = \MenuManager\Vendor\Composer\Pcre\PHPStan\PregMatchFlags::getType($flagsArg, $scope);
        if ($flagsType === null) {
            return null;
        }
        if (\stripos($methodReflection->getName(), 'matchAll') !== \false) {
            return $this->regexShapeMatcher->matchAllExpr($patternArg->value, $flagsType, TrinaryLogic::createMaybe(), $scope);
        }
        return $this->regexShapeMatcher->matchExpr($patternArg->value, $flagsType, TrinaryLogic::createMaybe(), $scope);
    }
}
