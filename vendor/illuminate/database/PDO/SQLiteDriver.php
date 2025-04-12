<?php

namespace MenuManager\Vendor\Illuminate\Database\PDO;

use MenuManager\Vendor\Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use MenuManager\Vendor\Illuminate\Database\PDO\Concerns\ConnectsToDatabase;
class SQLiteDriver extends AbstractSQLiteDriver
{
    use ConnectsToDatabase;
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_sqlite';
    }
}
