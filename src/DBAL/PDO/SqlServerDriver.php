<?php

namespace KitLoong\MigrationsGenerator\DBAL\PDO;

use Doctrine\DBAL\Driver\AbstractSQLServerDriver;
use Doctrine\DBAL\Driver\Connection as ConnectionContract;

class SqlServerDriver extends AbstractSQLServerDriver
{
    /**
     * Create a new database connection.
     *
     * @param  mixed[]  $params
     * @return \KitLoong\MigrationsGenerator\DBAL\PDO\SqlServerConnection
     */
    public function connect(array $params): ConnectionContract
    {
        return new SqlServerConnection(
            new Connection($params['pdo'])
        );
    }

    public function getName(): string
    {
        return 'pdo_sqlsrv';
    }
}
