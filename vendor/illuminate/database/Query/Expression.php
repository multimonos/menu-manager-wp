<?php

namespace MenuManager\Vendor\Illuminate\Database\Query;

use MenuManager\Vendor\Illuminate\Contracts\Database\Query\Expression as ExpressionContract;
use MenuManager\Vendor\Illuminate\Database\Grammar;
class Expression implements ExpressionContract
{
    /**
     * The value of the expression.
     *
     * @var string|int|float
     */
    protected $value;
    /**
     * Create a new raw query expression.
     *
     * @param  string|int|float  $value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
    /**
     * Get the value of the expression.
     *
     * @param  \Illuminate\Database\Grammar  $grammar
     * @return string|int|float
     */
    public function getValue(Grammar $grammar)
    {
        return $this->value;
    }
}
