<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Composer\Pcre\PHPStan;

use MenuManager\Vendor\Composer\Pcre\Preg;
use MenuManager\Vendor\PhpParser\Node\Expr\StaticCall;
use MenuManager\Vendor\PHPStan\Analyser\Scope;
use MenuManager\Vendor\PHPStan\Analyser\SpecifiedTypes;
use MenuManager\Vendor\PHPStan\Analyser\TypeSpecifier;
use MenuManager\Vendor\PHPStan\Analyser\TypeSpecifierAwareExtension;
use MenuManager\Vendor\PHPStan\Analyser\TypeSpecifierContext;
use MenuManager\Vendor\PHPStan\Reflection\MethodReflection;
use MenuManager\Vendor\PHPStan\TrinaryLogic;
use MenuManager\Vendor\PHPStan\Type\Constant\ConstantArrayType;
use MenuManager\Vendor\PHPStan\Type\Php\RegexArrayShapeMatcher;
use MenuManager\Vendor\PHPStan\Type\StaticMethodTypeSpecifyingExtension;
use MenuManager\Vendor\PHPStan\Type\TypeCombinator;
use MenuManager\Vendor\PHPStan\Type\Type;
final class PregMatchTypeSpecifyingExtension implements StaticMethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /**
     * @var TypeSpecifier
     */
    private $typeSpecifier;
    /**
     * @var RegexArrayShapeMatcher
     */
    private $regexShapeMatcher;
    public function __construct(RegexArrayShapeMatcher $regexShapeMatcher)
    {
        $this->regexShapeMatcher = $regexShapeMatcher;
    }
    public function setTypeSpecifier(TypeSpecifier $typeSpecifier) : void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
    public function getClass() : string
    {
        return Preg::class;
    }
    public function isStaticMethodSupported(MethodReflection $methodReflection, StaticCall $node, TypeSpecifierContext $context) : bool
    {
        return \in_array($methodReflection->getName(), ['match', 'isMatch', 'matchStrictGroups', 'isMatchStrictGroups', 'matchAll', 'isMatchAll', 'matchAllStrictGroups', 'isMatchAllStrictGroups'], \true) && !$context->null();
    }
    public function specifyTypes(MethodReflection $methodReflection, StaticCall $node, Scope $scope, TypeSpecifierContext $context) : SpecifiedTypes
    {
        $args = $node->getArgs();
        $patternArg = $args[0] ?? null;
        $matchesArg = $args[2] ?? null;
        $flagsArg = $args[3] ?? null;
        if ($patternArg === null || $matchesArg === null) {
            return new SpecifiedTypes();
        }
        $flagsType = \MenuManager\Vendor\Composer\Pcre\PHPStan\PregMatchFlags::getType($flagsArg, $scope);
        if ($flagsType === null) {
            return new SpecifiedTypes();
        }
        if (\stripos($methodReflection->getName(), 'matchAll') !== \false) {
            $matchedType = $this->regexShapeMatcher->matchAllExpr($patternArg->value, $flagsType, TrinaryLogic::createFromBoolean($context->true()), $scope);
        } else {
            $matchedType = $this->regexShapeMatcher->matchExpr($patternArg->value, $flagsType, TrinaryLogic::createFromBoolean($context->true()), $scope);
        }
        if ($matchedType === null) {
            return new SpecifiedTypes();
        }
        if (\in_array($methodReflection->getName(), ['matchStrictGroups', 'isMatchStrictGroups', 'matchAllStrictGroups', 'isMatchAllStrictGroups'], \true)) {
            $matchedType = \MenuManager\Vendor\Composer\Pcre\PHPStan\PregMatchFlags::removeNullFromMatches($matchedType);
        }
        $overwrite = \false;
        if ($context->false()) {
            $overwrite = \true;
            $context = $context->negate();
        }
        // @phpstan-ignore function.alreadyNarrowedType
        if (\method_exists('MenuManager\\Vendor\\PHPStan\\Analyser\\SpecifiedTypes', 'setRootExpr')) {
            $typeSpecifier = $this->typeSpecifier->create($matchesArg->value, $matchedType, $context, $scope)->setRootExpr($node);
            return $overwrite ? $typeSpecifier->setAlwaysOverwriteTypes() : $typeSpecifier;
        }
        // @phpstan-ignore arguments.count
        return $this->typeSpecifier->create(
            $matchesArg->value,
            $matchedType,
            $context,
            // @phpstan-ignore argument.type
            $overwrite,
            $scope,
            $node
        );
    }
}
