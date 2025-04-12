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

use MenuManager\Vendor\PhpParser\NodeTraverser;
use MenuManager\Vendor\PhpParser\NodeVisitor;
use MenuManager\Vendor\PhpParser\Parser;
use MenuManager\Vendor\PhpParser\ParserFactory;
use MenuManager\Vendor\Symfony\Component\Finder\Finder;
use MenuManager\Vendor\Symfony\Component\Translation\Extractor\Visitor\AbstractVisitor;
use MenuManager\Vendor\Symfony\Component\Translation\MessageCatalogue;
/**
 * PhpAstExtractor extracts translation messages from a PHP AST.
 *
 * @author Mathieu Santostefano <msantostefano@protonmail.com>
 */
final class PhpAstExtractor extends \MenuManager\Vendor\Symfony\Component\Translation\Extractor\AbstractFileExtractor implements \MenuManager\Vendor\Symfony\Component\Translation\Extractor\ExtractorInterface
{
    private Parser $parser;
    public function __construct(
        /**
         * @param iterable<AbstractVisitor&NodeVisitor> $visitors
         */
        private readonly iterable $visitors,
        private string $prefix = ''
    )
    {
        if (!\class_exists(ParserFactory::class)) {
            throw new \LogicException(\sprintf('You cannot use "%s" as the "nikic/php-parser" package is not installed. Try running "composer require nikic/php-parser".', static::class));
        }
        $this->parser = (new ParserFactory())->createForHostVersion();
    }
    public function extract(iterable|string $resource, MessageCatalogue $catalogue) : void
    {
        foreach ($this->extractFiles($resource) as $file) {
            $traverser = new NodeTraverser();
            // This is needed to resolve namespaces in class methods/constants.
            $nameResolver = new NodeVisitor\NameResolver();
            $traverser->addVisitor($nameResolver);
            /** @var AbstractVisitor&NodeVisitor $visitor */
            foreach ($this->visitors as $visitor) {
                $visitor->initialize($catalogue, $file, $this->prefix);
                $traverser->addVisitor($visitor);
            }
            $nodes = $this->parser->parse(\file_get_contents($file));
            $traverser->traverse($nodes);
        }
    }
    public function setPrefix(string $prefix) : void
    {
        $this->prefix = $prefix;
    }
    protected function canBeExtracted(string $file) : bool
    {
        return 'php' === \pathinfo($file, \PATHINFO_EXTENSION) && $this->isFile($file) && \preg_match('/\\bt\\(|->trans\\(|TranslatableMessage|Symfony\\\\Component\\\\Validator\\\\Constraints/i', \file_get_contents($file));
    }
    protected function extractFromDirectory(array|string $resource) : iterable|Finder
    {
        if (!\class_exists(Finder::class)) {
            throw new \LogicException(\sprintf('You cannot use "%s" as the "symfony/finder" package is not installed. Try running "composer require symfony/finder".', static::class));
        }
        return (new Finder())->files()->name('*.php')->in($resource);
    }
}
