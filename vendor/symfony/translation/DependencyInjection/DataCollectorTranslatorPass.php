<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MenuManager\Vendor\Symfony\Component\Translation\DependencyInjection;

use MenuManager\Vendor\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use MenuManager\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use MenuManager\Vendor\Symfony\Component\Translation\TranslatorBagInterface;
/**
 * @author Christian Flothmann <christian.flothmann@sensiolabs.de>
 */
class DataCollectorTranslatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        if (!$container->has('translator')) {
            return;
        }
        $translatorClass = $container->getParameterBag()->resolveValue($container->findDefinition('translator')->getClass());
        if (!\is_subclass_of($translatorClass, TranslatorBagInterface::class)) {
            $container->removeDefinition('translator.data_collector');
            $container->removeDefinition('data_collector.translation');
        }
    }
}
