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

if (!\function_exists(\MenuManager\Vendor\Symfony\Component\Translation\t::class)) {
    /**
     * @author Nate Wiebe <nate@northern.co>
     */
    function t(string $message, array $parameters = [], ?string $domain = null) : \MenuManager\Vendor\Symfony\Component\Translation\TranslatableMessage
    {
        return new \MenuManager\Vendor\Symfony\Component\Translation\TranslatableMessage($message, $parameters, $domain);
    }
}
