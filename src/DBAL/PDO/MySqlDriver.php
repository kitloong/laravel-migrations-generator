<?php

namespace KitLoong\MigrationsGenerator\DBAL\PDO;

use Doctrine\DBAL\Driver\AbstractMySQLDriver;

class MySqlDriver extends AbstractMySQLDriver
{
    use ConnectsToDatabase;

    public function getName(): string
    {
        return 'pdo_mysql';
    }
}
