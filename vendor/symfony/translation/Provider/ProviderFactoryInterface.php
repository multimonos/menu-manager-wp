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

use MenuManager\Vendor\Symfony\Component\Translation\Exception\IncompleteDsnException;
use MenuManager\Vendor\Symfony\Component\Translation\Exception\UnsupportedSchemeException;
interface ProviderFactoryInterface
{
    /**
     * @throws UnsupportedSchemeException
     * @throws IncompleteDsnException
     */
    public function create(\MenuManager\Vendor\Symfony\Component\Translation\Provider\Dsn $dsn) : \MenuManager\Vendor\Symfony\Component\Translation\Provider\ProviderInterface;
    public function supports(\MenuManager\Vendor\Symfony\Component\Translation\Provider\Dsn $dsn) : bool;
}
