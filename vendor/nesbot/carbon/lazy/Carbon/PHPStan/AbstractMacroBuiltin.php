<?php

declare (strict_types=1);
/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MenuManager\Vendor\Carbon\PHPStan;

use MenuManager\Vendor\PHPStan\BetterReflection\Reflection;
use ReflectionMethod;
if (!\class_exists(\MenuManager\Vendor\Carbon\PHPStan\AbstractReflectionMacro::class, \false)) {
    abstract class AbstractReflectionMacro extends \MenuManager\Vendor\Carbon\PHPStan\AbstractMacro
    {
        /**
         * {@inheritdoc}
         */
        public function getReflection() : ?ReflectionMethod
        {
            if ($this->reflectionFunction instanceof Reflection\ReflectionMethod) {
                return new Reflection\Adapter\ReflectionMethod($this->reflectionFunction);
            }
            return $this->reflectionFunction instanceof ReflectionMethod ? $this->reflectionFunction : null;
        }
    }
}
