<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MenuManager\Vendor\Symfony\Component\Translation\Formatter;

use MenuManager\Vendor\Symfony\Component\Translation\IdentityTranslator;
use MenuManager\Vendor\Symfony\Contracts\Translation\TranslatorInterface;
// Help opcache.preload discover always-needed symbols
\class_exists(\MenuManager\Vendor\Symfony\Component\Translation\Formatter\IntlFormatter::class);
/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class MessageFormatter implements \MenuManager\Vendor\Symfony\Component\Translation\Formatter\MessageFormatterInterface, \MenuManager\Vendor\Symfony\Component\Translation\Formatter\IntlFormatterInterface
{
    private TranslatorInterface $translator;
    private \MenuManager\Vendor\Symfony\Component\Translation\Formatter\IntlFormatterInterface $intlFormatter;
    /**
     * @param TranslatorInterface|null $translator An identity translator to use as selector for pluralization
     */
    public function __construct(?TranslatorInterface $translator = null, ?\MenuManager\Vendor\Symfony\Component\Translation\Formatter\IntlFormatterInterface $intlFormatter = null)
    {
        $this->translator = $translator ?? new IdentityTranslator();
        $this->intlFormatter = $intlFormatter ?? new \MenuManager\Vendor\Symfony\Component\Translation\Formatter\IntlFormatter();
    }
    public function format(string $message, string $locale, array $parameters = []) : string
    {
        return $this->translator->trans($message, $parameters, null, $locale);
    }
    public function formatIntl(string $message, string $locale, array $parameters = []) : string
    {
        return $this->intlFormatter->formatIntl($message, $locale, $parameters);
    }
}
