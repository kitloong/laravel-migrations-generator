<?php

namespace KitLoong\MigrationsGenerator\DBAL\PDO\Concerns;

use Doctrine\DBAL\Driver\Connection as ConnectionContract;
use InvalidArgumentException;
use KitLoong\MigrationsGenerator\DBAL\PDO\Connection;
use PDO;

trait ConnectsToDatabase
{
    /**
     * Create a new database connection.
     *
     * @param  mixed[]  $params
     * @return \KitLoong\MigrationsGenerator\DBAL\PDO\Connection
     * @throws \InvalidArgumentException
     */
    public function connect(array $params): ConnectionContract
    {
        if (! isset($params['pdo']) || ! $params['pdo'] instanceof PDO) {
            throw new InvalidArgumentException('Laravel requires the "pdo" property to be set and be a PDO instance.');
        }

        return new Connection($params['pdo']);
    }
}
