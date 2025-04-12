<?php

namespace MenuManager\Vendor\Illuminate\Database\PDO;

use MenuManager\Vendor\Doctrine\DBAL\Driver\AbstractSQLServerDriver;
class SqlServerDriver extends AbstractSQLServerDriver
{
    /**
     * Create a new database connection.
     *
     * @param  mixed[]  $params
     * @param  string|null  $username
     * @param  string|null  $password
     * @param  mixed[]  $driverOptions
     * @return \Illuminate\Database\PDO\SqlServerConnection
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        return new \MenuManager\Vendor\Illuminate\Database\PDO\SqlServerConnection(new \MenuManager\Vendor\Illuminate\Database\PDO\Connection($params['pdo']));
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_sqlsrv';
    }
}
