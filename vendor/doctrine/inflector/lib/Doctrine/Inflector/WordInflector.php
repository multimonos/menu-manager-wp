<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Doctrine\Inflector;

interface WordInflector
{
    public function inflect(string $word) : string;
}
