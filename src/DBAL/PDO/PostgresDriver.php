<?php

namespace KitLoong\MigrationsGenerator\DBAL\PDO;

use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use KitLoong\MigrationsGenerator\DBAL\PDO\Concerns\ConnectsToDatabase;

class PostgresDriver extends AbstractPostgreSQLDriver
{
    use ConnectsToDatabase;

    public function getName(): string
    {
        return 'pdo_pgsql';
    }
}
