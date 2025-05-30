<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MenuManager\Vendor\Symfony\Component\Translation\Dumper;

use MenuManager\Vendor\Symfony\Component\Translation\MessageCatalogue;
/**
 * PhpFileDumper generates PHP files from a message catalogue.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class PhpFileDumper extends \MenuManager\Vendor\Symfony\Component\Translation\Dumper\FileDumper
{
    public function formatCatalogue(MessageCatalogue $messages, string $domain, array $options = []) : string
    {
        return "<?php\n\nreturn " . \var_export($messages->all($domain), \true) . ";\n";
    }
    protected function getExtension() : string
    {
        return 'php';
    }
}
