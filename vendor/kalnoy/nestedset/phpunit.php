<?php

namespace MenuManager\Vendor;

include __DIR__ . '/vendor/autoload.php';
$capsule = new \MenuManager\Vendor\Illuminate\Database\Capsule\Manager();
$capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => 'prfx_']);
$capsule->setEventDispatcher(new \MenuManager\Vendor\Illuminate\Events\Dispatcher());
$capsule->bootEloquent();
$capsule->setAsGlobal();
include __DIR__ . '/tests/models/Category.php';
include __DIR__ . '/tests/models/MenuItem.php';
