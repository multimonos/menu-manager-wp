<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Doctrine\Inflector\Rules;

final class Substitution
{
    /** @var Word */
    private $from;
    /** @var Word */
    private $to;
    public function __construct(\MenuManager\Vendor\Doctrine\Inflector\Rules\Word $from, \MenuManager\Vendor\Doctrine\Inflector\Rules\Word $to)
    {
        $this->from = $from;
        $this->to = $to;
    }
    public function getFrom() : \MenuManager\Vendor\Doctrine\Inflector\Rules\Word
    {
        return $this->from;
    }
    public function getTo() : \MenuManager\Vendor\Doctrine\Inflector\Rules\Word
    {
        return $this->to;
    }
}
