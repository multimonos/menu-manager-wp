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
abstract class AbstractProviderFactory implements \MenuManager\Vendor\Symfony\Component\Translation\Provider\ProviderFactoryInterface
{
    public function supports(\MenuManager\Vendor\Symfony\Component\Translation\Provider\Dsn $dsn) : bool
    {
        return \in_array($dsn->getScheme(), $this->getSupportedSchemes(), \true);
    }
    /**
     * @return string[]
     */
    protected abstract function getSupportedSchemes() : array;
    protected function getUser(\MenuManager\Vendor\Symfony\Component\Translation\Provider\Dsn $dsn) : string
    {
        return $dsn->getUser() ?? throw new IncompleteDsnException('User is not set.', $dsn->getScheme() . '://' . $dsn->getHost());
    }
    protected function getPassword(\MenuManager\Vendor\Symfony\Component\Translation\Provider\Dsn $dsn) : string
    {
        return $dsn->getPassword() ?? throw new IncompleteDsnException('Password is not set.', $dsn->getOriginalDsn());
    }
}
