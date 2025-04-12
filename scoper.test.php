<?php

use MenuManager\Foobar;
use MenuManager\Vendor\League\Csv\Reader;

require_once __DIR__ . '/vendor/scoper-autoload.php';

$reader = Reader::createFromPath( './data/valid_create.csv', 'r' );

$records = $reader->getRecords();

foreach ( $records as $record ) {
    echo "\n" . print_r( $record, true );
}

Foobar::hi();