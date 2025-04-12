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

use MenuManager\Vendor\Symfony\Component\Translation\Catalogue\AbstractOperation;
use MenuManager\Vendor\Symfony\Component\Translation\Catalogue\TargetOperation;
final class TranslatorBag implements \MenuManager\Vendor\Symfony\Component\Translation\TranslatorBagInterface
{
    /** @var MessageCatalogue[] */
    private array $catalogues = [];
    public function addCatalogue(\MenuManager\Vendor\Symfony\Component\Translation\MessageCatalogue $catalogue) : void
    {
        if (null !== ($existingCatalogue = $this->getCatalogue($catalogue->getLocale()))) {
            $catalogue->addCatalogue($existingCatalogue);
        }
        $this->catalogues[$catalogue->getLocale()] = $catalogue;
    }
    public function addBag(\MenuManager\Vendor\Symfony\Component\Translation\TranslatorBagInterface $bag) : void
    {
        foreach ($bag->getCatalogues() as $catalogue) {
            $this->addCatalogue($catalogue);
        }
    }
    public function getCatalogue(?string $locale = null) : \MenuManager\Vendor\Symfony\Component\Translation\MessageCatalogueInterface
    {
        if (null === $locale || !isset($this->catalogues[$locale])) {
            $this->catalogues[$locale] = new \MenuManager\Vendor\Symfony\Component\Translation\MessageCatalogue($locale);
        }
        return $this->catalogues[$locale];
    }
    public function getCatalogues() : array
    {
        return \array_values($this->catalogues);
    }
    public function diff(\MenuManager\Vendor\Symfony\Component\Translation\TranslatorBagInterface $diffBag) : self
    {
        $diff = new self();
        foreach ($this->catalogues as $locale => $catalogue) {
            if (null === ($diffCatalogue = $diffBag->getCatalogue($locale))) {
                $diff->addCatalogue($catalogue);
                continue;
            }
            $operation = new TargetOperation($diffCatalogue, $catalogue);
            $operation->moveMessagesToIntlDomainsIfPossible(AbstractOperation::NEW_BATCH);
            $newCatalogue = new \MenuManager\Vendor\Symfony\Component\Translation\MessageCatalogue($locale);
            foreach ($catalogue->getDomains() as $domain) {
                $newCatalogue->add($operation->getNewMessages($domain), $domain);
            }
            $diff->addCatalogue($newCatalogue);
        }
        return $diff;
    }
    public function intersect(\MenuManager\Vendor\Symfony\Component\Translation\TranslatorBagInterface $intersectBag) : self
    {
        $diff = new self();
        foreach ($this->catalogues as $locale => $catalogue) {
            if (null === ($intersectCatalogue = $intersectBag->getCatalogue($locale))) {
                continue;
            }
            $operation = new TargetOperation($catalogue, $intersectCatalogue);
            $operation->moveMessagesToIntlDomainsIfPossible(AbstractOperation::OBSOLETE_BATCH);
            $obsoleteCatalogue = new \MenuManager\Vendor\Symfony\Component\Translation\MessageCatalogue($locale);
            foreach ($operation->getDomains() as $domain) {
                $obsoleteCatalogue->add(\array_diff($operation->getMessages($domain), $operation->getNewMessages($domain)), $domain);
            }
            $diff->addCatalogue($obsoleteCatalogue);
        }
        return $diff;
    }
}
