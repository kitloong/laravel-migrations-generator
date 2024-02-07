<?php

namespace KitLoong\MigrationsGenerator\DBAL;

use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\DBAL\PDO\MySqlDriver;
use KitLoong\MigrationsGenerator\DBAL\PDO\PostgresDriver;
use KitLoong\MigrationsGenerator\DBAL\PDO\SQLiteDriver;
use KitLoong\MigrationsGenerator\DBAL\PDO\SqlServerDriver;

/**
 * @template T of \Doctrine\DBAL\Platforms\AbstractPlatform
 */
class Connection
{
    /**
     * The instance of Doctrine connection.
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $doctrineConnection;

    /**
     * Get the Doctrine DBAL database connection instance.
     */
    public function getDoctrineConnection(): DoctrineConnection
    {
        if (method_exists(DB::connection(), 'getDoctrineConnection')) {
            // @phpstan-ignore-next-line
            return DB::getDoctrineConnection();
        }

        if ($this->doctrineConnection === null) {
            $driver = $this->getDoctrineDriver();

            $this->doctrineConnection = new DoctrineConnection(array_filter([
                'pdo'           => DB::connection()->getPdo(),
                'dbname'        => DB::connection()->getDatabaseName(),
                'driver'        => $driver->getName(),
                'serverVersion' => DB::connection()->getConfig('server_version'),
            ]), $driver);
        }

        return $this->doctrineConnection;
    }

    /**
     * Get the Doctrine DBAL schema manager for the connection.
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager<T>
     * @throws \Doctrine\DBAL\Exception
     */
    public function getDoctrineSchemaManager(): AbstractSchemaManager
    {
        if (method_exists($this->getDoctrineConnection(), 'createSchemaManager')) {
            return $this->getDoctrineConnection()->createSchemaManager();
        }

        // @codeCoverageIgnoreStart
        // @phpstan-ignore-next-line
        return $this->getDoctrineConnection()->getSchemaManager();
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get the Doctrine DBAL driver.
     */
    protected function getDoctrineDriver(): SQLiteDriver|SqlServerDriver|MySqlDriver|PostgresDriver
    {
        switch (true) {
            case DB::connection() instanceof MySqlConnection:
                return new MySqlDriver();

            case DB::connection() instanceof SqlServerConnection:
                return new SqlServerDriver();

            case DB::connection() instanceof PostgresConnection:
                return new PostgresDriver();

            default:
                return new SQLiteDriver();
        }
    }
}
