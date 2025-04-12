<?php

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MenuManager\Vendor\Carbon\PHPStan;

use MenuManager\Vendor\Carbon\CarbonInterface;
use MenuManager\Vendor\PHPStan\Reflection\ReflectionProvider;
use ReflectionClass;
use ReflectionException;
final class MacroScanner
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * MacroScanner constructor.
     *
     * @param \PHPStan\Reflection\ReflectionProvider $reflectionProvider
     */
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * Return true if the given pair class-method is a Carbon macro.
     *
     * @param class-string $className
     * @param string       $methodName
     *
     * @return bool
     */
    public function hasMethod(string $className, string $methodName) : bool
    {
        $classReflection = $this->reflectionProvider->getClass($className);
        if ($classReflection->getName() !== CarbonInterface::class && !$classReflection->isSubclassOf(CarbonInterface::class)) {
            return \false;
        }
        return \is_callable([$className, 'hasMacro']) && $className::hasMacro($methodName);
    }
    /**
     * Return the Macro for a given pair class-method.
     *
     * @param class-string $className
     * @param string       $methodName
     *
     * @throws ReflectionException
     *
     * @return Macro
     */
    public function getMethod(string $className, string $methodName) : \MenuManager\Vendor\Carbon\PHPStan\Macro
    {
        $reflectionClass = new ReflectionClass($className);
        $property = $reflectionClass->getProperty('globalMacros');
        $property->setAccessible(\true);
        $macro = $property->getValue()[$methodName];
        return new \MenuManager\Vendor\Carbon\PHPStan\Macro($className, $methodName, $macro);
    }
}
