<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Doctrine\Inflector;

use MenuManager\Vendor\Doctrine\Inflector\Rules\Ruleset;
use function array_unshift;
abstract class GenericLanguageInflectorFactory implements \MenuManager\Vendor\Doctrine\Inflector\LanguageInflectorFactory
{
    /** @var Ruleset[] */
    private $singularRulesets = [];
    /** @var Ruleset[] */
    private $pluralRulesets = [];
    public final function __construct()
    {
        $this->singularRulesets[] = $this->getSingularRuleset();
        $this->pluralRulesets[] = $this->getPluralRuleset();
    }
    public final function build() : \MenuManager\Vendor\Doctrine\Inflector\Inflector
    {
        return new \MenuManager\Vendor\Doctrine\Inflector\Inflector(new \MenuManager\Vendor\Doctrine\Inflector\CachedWordInflector(new \MenuManager\Vendor\Doctrine\Inflector\RulesetInflector(...$this->singularRulesets)), new \MenuManager\Vendor\Doctrine\Inflector\CachedWordInflector(new \MenuManager\Vendor\Doctrine\Inflector\RulesetInflector(...$this->pluralRulesets)));
    }
    public final function withSingularRules(?Ruleset $singularRules, bool $reset = \false) : \MenuManager\Vendor\Doctrine\Inflector\LanguageInflectorFactory
    {
        if ($reset) {
            $this->singularRulesets = [];
        }
        if ($singularRules instanceof Ruleset) {
            array_unshift($this->singularRulesets, $singularRules);
        }
        return $this;
    }
    public final function withPluralRules(?Ruleset $pluralRules, bool $reset = \false) : \MenuManager\Vendor\Doctrine\Inflector\LanguageInflectorFactory
    {
        if ($reset) {
            $this->pluralRulesets = [];
        }
        if ($pluralRules instanceof Ruleset) {
            array_unshift($this->pluralRulesets, $pluralRules);
        }
        return $this;
    }
    protected abstract function getSingularRuleset() : Ruleset;
    protected abstract function getPluralRuleset() : Ruleset;
}
