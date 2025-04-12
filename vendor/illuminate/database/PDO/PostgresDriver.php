<?php

namespace MenuManager\Vendor\Illuminate\Database\PDO;

use MenuManager\Vendor\Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use MenuManager\Vendor\Illuminate\Database\PDO\Concerns\ConnectsToDatabase;
class PostgresDriver extends AbstractPostgreSQLDriver
{
    use ConnectsToDatabase;
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_pgsql';
    }
}
