<?php

use MenuManager\Foobar;
use MenuManager\Vendor\League\Csv\Writer;

require_once __DIR__ . '/vendor/scoper-autoload.php';

$writer = Writer::createFromPath( 'test.csv', 'w' );
$writer->insertOne( ["foo"] );
Foobar::hi();