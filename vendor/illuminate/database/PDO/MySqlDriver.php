<?php

namespace MenuManager\Vendor\Illuminate\Database\PDO;

use MenuManager\Vendor\Doctrine\DBAL\Driver\AbstractMySQLDriver;
use MenuManager\Vendor\Illuminate\Database\PDO\Concerns\ConnectsToDatabase;
class MySqlDriver extends AbstractMySQLDriver
{
    use ConnectsToDatabase;
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_mysql';
    }
}
