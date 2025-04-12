<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MenuManager\Vendor\Symfony\Component\Translation\Provider;

use MenuManager\Vendor\Symfony\Component\Translation\Exception\UnsupportedSchemeException;
/**
 * @author Mathieu Santostefano <msantostefano@protonmail.com>
 */
class TranslationProviderCollectionFactory
{
    private iterable $factories;
    private array $enabledLocales;
    /**
     * @param iterable<mixed, ProviderFactoryInterface> $factories
     */
    public function __construct(iterable $factories, array $enabledLocales)
    {
        $this->factories = $factories;
        $this->enabledLocales = $enabledLocales;
    }
    public function fromConfig(array $config) : \MenuManager\Vendor\Symfony\Component\Translation\Provider\TranslationProviderCollection
    {
        $providers = [];
        foreach ($config as $name => $currentConfig) {
            $providers[$name] = $this->fromDsnObject(new \MenuManager\Vendor\Symfony\Component\Translation\Provider\Dsn($currentConfig['dsn']), !$currentConfig['locales'] ? $this->enabledLocales : $currentConfig['locales'], !$currentConfig['domains'] ? [] : $currentConfig['domains']);
        }
        return new \MenuManager\Vendor\Symfony\Component\Translation\Provider\TranslationProviderCollection($providers);
    }
    public function fromDsnObject(\MenuManager\Vendor\Symfony\Component\Translation\Provider\Dsn $dsn, array $locales, array $domains = []) : \MenuManager\Vendor\Symfony\Component\Translation\Provider\ProviderInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn)) {
                return new \MenuManager\Vendor\Symfony\Component\Translation\Provider\FilteringProvider($factory->create($dsn), $locales, $domains);
            }
        }
        throw new UnsupportedSchemeException($dsn);
    }
}
