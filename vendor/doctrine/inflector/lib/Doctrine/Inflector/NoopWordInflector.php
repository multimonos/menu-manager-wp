<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Doctrine\Inflector;

class NoopWordInflector implements \MenuManager\Vendor\Doctrine\Inflector\WordInflector
{
    public function inflect(string $word) : string
    {
        return $word;
    }
}
