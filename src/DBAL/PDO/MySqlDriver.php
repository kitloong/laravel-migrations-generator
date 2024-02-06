<?php

namespace KitLoong\MigrationsGenerator\DBAL\PDO;

use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use KitLoong\MigrationsGenerator\DBAL\PDO\Concerns\ConnectsToDatabase;

class MySqlDriver extends AbstractMySQLDriver
{
    use ConnectsToDatabase;

    public function getName(): string
    {
        return 'pdo_mysql';
    }
}
