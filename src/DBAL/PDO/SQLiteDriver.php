<?php

namespace KitLoong\MigrationsGenerator\DBAL\PDO;

use Doctrine\DBAL\Driver\AbstractSQLiteDriver;

class SQLiteDriver extends AbstractSQLiteDriver
{
    use ConnectsToDatabase;

    public function getName(): string
    {
        return 'pdo_sqlite';
    }
}
