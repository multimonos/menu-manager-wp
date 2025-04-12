<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Doctrine\Inflector\Rules\Spanish;

use MenuManager\Vendor\Doctrine\Inflector\Rules\Patterns;
use MenuManager\Vendor\Doctrine\Inflector\Rules\Ruleset;
use MenuManager\Vendor\Doctrine\Inflector\Rules\Substitutions;
use MenuManager\Vendor\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset() : Ruleset
    {
        return new Ruleset(new Transformations(...\MenuManager\Vendor\Doctrine\Inflector\Rules\Spanish\Inflectible::getSingular()), new Patterns(...\MenuManager\Vendor\Doctrine\Inflector\Rules\Spanish\Uninflected::getSingular()), (new Substitutions(...\MenuManager\Vendor\Doctrine\Inflector\Rules\Spanish\Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset() : Ruleset
    {
        return new Ruleset(new Transformations(...\MenuManager\Vendor\Doctrine\Inflector\Rules\Spanish\Inflectible::getPlural()), new Patterns(...\MenuManager\Vendor\Doctrine\Inflector\Rules\Spanish\Uninflected::getPlural()), new Substitutions(...\MenuManager\Vendor\Doctrine\Inflector\Rules\Spanish\Inflectible::getIrregular()));
    }
}
