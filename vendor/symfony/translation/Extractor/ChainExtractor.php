<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MenuManager\Vendor\Symfony\Component\Translation\Extractor;

use MenuManager\Vendor\Symfony\Component\Translation\MessageCatalogue;
/**
 * ChainExtractor extracts translation messages from template files.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class ChainExtractor implements \MenuManager\Vendor\Symfony\Component\Translation\Extractor\ExtractorInterface
{
    /**
     * The extractors.
     *
     * @var ExtractorInterface[]
     */
    private array $extractors = [];
    /**
     * Adds a loader to the translation extractor.
     *
     * @return void
     */
    public function addExtractor(string $format, \MenuManager\Vendor\Symfony\Component\Translation\Extractor\ExtractorInterface $extractor)
    {
        $this->extractors[$format] = $extractor;
    }
    /**
     * @return void
     */
    public function setPrefix(string $prefix)
    {
        foreach ($this->extractors as $extractor) {
            $extractor->setPrefix($prefix);
        }
    }
    /**
     * @return void
     */
    public function extract(string|iterable $directory, MessageCatalogue $catalogue)
    {
        foreach ($this->extractors as $extractor) {
            $extractor->extract($directory, $catalogue);
        }
    }
}
