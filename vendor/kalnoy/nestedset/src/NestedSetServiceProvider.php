<?php

namespace MenuManager\Vendor\Kalnoy\Nestedset;

use MenuManager\Vendor\Illuminate\Database\Schema\Blueprint;
use MenuManager\Vendor\Illuminate\Support\ServiceProvider;
class NestedSetServiceProvider extends ServiceProvider
{
    public function register()
    {
        Blueprint::macro('nestedSet', function () {
            \MenuManager\Vendor\Kalnoy\Nestedset\NestedSet::columns($this);
        });
        Blueprint::macro('dropNestedSet', function () {
            \MenuManager\Vendor\Kalnoy\Nestedset\NestedSet::dropColumns($this);
        });
    }
}
