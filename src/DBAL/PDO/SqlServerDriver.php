<?php

namespace KitLoong\MigrationsGenerator\DBAL\PDO;

use Doctrine\DBAL\Driver\AbstractSQLServerDriver;

class SqlServerDriver extends AbstractSQLServerDriver
{
    use ConnectsToDatabase;

    public function getName(): string
    {
        return 'pdo_sqlsrv';
    }
}
