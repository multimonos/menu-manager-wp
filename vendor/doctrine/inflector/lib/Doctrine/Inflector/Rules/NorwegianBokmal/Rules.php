<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Doctrine\Inflector\Rules\NorwegianBokmal;

use MenuManager\Vendor\Doctrine\Inflector\Rules\Patterns;
use MenuManager\Vendor\Doctrine\Inflector\Rules\Ruleset;
use MenuManager\Vendor\Doctrine\Inflector\Rules\Substitutions;
use MenuManager\Vendor\Doctrine\Inflector\Rules\Transformations;
final class Rules
{
    public static function getSingularRuleset() : Ruleset
    {
        return new Ruleset(new Transformations(...\MenuManager\Vendor\Doctrine\Inflector\Rules\NorwegianBokmal\Inflectible::getSingular()), new Patterns(...\MenuManager\Vendor\Doctrine\Inflector\Rules\NorwegianBokmal\Uninflected::getSingular()), (new Substitutions(...\MenuManager\Vendor\Doctrine\Inflector\Rules\NorwegianBokmal\Inflectible::getIrregular()))->getFlippedSubstitutions());
    }
    public static function getPluralRuleset() : Ruleset
    {
        return new Ruleset(new Transformations(...\MenuManager\Vendor\Doctrine\Inflector\Rules\NorwegianBokmal\Inflectible::getPlural()), new Patterns(...\MenuManager\Vendor\Doctrine\Inflector\Rules\NorwegianBokmal\Uninflected::getPlural()), new Substitutions(...\MenuManager\Vendor\Doctrine\Inflector\Rules\NorwegianBokmal\Inflectible::getIrregular()));
    }
}
