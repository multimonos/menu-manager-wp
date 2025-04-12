<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Doctrine\Inflector\Rules\French;

use MenuManager\Vendor\Doctrine\Inflector\GenericLanguageInflectorFactory;
use MenuManager\Vendor\Doctrine\Inflector\Rules\Ruleset;
final class InflectorFactory extends GenericLanguageInflectorFactory
{
    protected function getSingularRuleset() : Ruleset
    {
        return \MenuManager\Vendor\Doctrine\Inflector\Rules\French\Rules::getSingularRuleset();
    }
    protected function getPluralRuleset() : Ruleset
    {
        return \MenuManager\Vendor\Doctrine\Inflector\Rules\French\Rules::getPluralRuleset();
    }
}
