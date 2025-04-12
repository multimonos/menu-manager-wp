<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Doctrine\Inflector\Rules;

class Ruleset
{
    /** @var Transformations */
    private $regular;
    /** @var Patterns */
    private $uninflected;
    /** @var Substitutions */
    private $irregular;
    public function __construct(\MenuManager\Vendor\Doctrine\Inflector\Rules\Transformations $regular, \MenuManager\Vendor\Doctrine\Inflector\Rules\Patterns $uninflected, \MenuManager\Vendor\Doctrine\Inflector\Rules\Substitutions $irregular)
    {
        $this->regular = $regular;
        $this->uninflected = $uninflected;
        $this->irregular = $irregular;
    }
    public function getRegular() : \MenuManager\Vendor\Doctrine\Inflector\Rules\Transformations
    {
        return $this->regular;
    }
    public function getUninflected() : \MenuManager\Vendor\Doctrine\Inflector\Rules\Patterns
    {
        return $this->uninflected;
    }
    public function getIrregular() : \MenuManager\Vendor\Doctrine\Inflector\Rules\Substitutions
    {
        return $this->irregular;
    }
}
