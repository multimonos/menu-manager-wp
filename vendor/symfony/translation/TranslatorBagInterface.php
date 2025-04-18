<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MenuManager\Vendor\Symfony\Component\Translation;

use MenuManager\Vendor\Symfony\Component\Translation\Exception\InvalidArgumentException;
/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
interface TranslatorBagInterface
{
    /**
     * Gets the catalogue by locale.
     *
     * @param string|null $locale The locale or null to use the default
     *
     * @throws InvalidArgumentException If the locale contains invalid characters
     */
    public function getCatalogue(?string $locale = null) : \MenuManager\Vendor\Symfony\Component\Translation\MessageCatalogueInterface;
    /**
     * Returns all catalogues of the instance.
     *
     * @return MessageCatalogueInterface[]
     */
    public function getCatalogues() : array;
}
