<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace KitLoong\MigrationsGenerator;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Generators\Platform;

class MigrationsGeneratorSetting
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $platform;

    /**
     * @var boolean
     */
    private $ignoreIndexNames;

    /**
     * @var boolean
     */
    private $ignoreForeignKeyNames;

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param  string  $connection
     */
    public function setConnection(string $connection): void
    {
        $this->connection = DB::connection($connection);

        /** @var \Doctrine\DBAL\Connection $doctConn */
        $doctConn = $this->connection->getDoctrineConnection();
        $classPath = explode('\\', get_class($doctConn->getDatabasePlatform()));
        $platform = end($classPath);

        switch (true) {
            case preg_match('/mysql/i', $platform) > 0:
                $this->platform = Platform::MYSQL;
                break;
            case preg_match('/postgresql/i', $platform) > 0:
                $this->platform = Platform::POSTGRESQL;
                break;
            case preg_match('/sqlserver/i', $platform) > 0:
                $this->platform = Platform::SQLSERVER;
                break;
            case preg_match('/sqlite/i', $platform) > 0:
                $this->platform = Platform::SQLITE;
                break;
            default:
                $this->platform = Platform::OTHERS;
                break;
        }
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * @return bool
     */
    public function isIgnoreIndexNames(): bool
    {
        return $this->ignoreIndexNames;
    }

    /**
     * @param  bool  $ignoreIndexNames
     */
    public function setIgnoreIndexNames(bool $ignoreIndexNames): void
    {
        $this->ignoreIndexNames = $ignoreIndexNames;
    }

    /**
     * @return bool
     */
    public function isIgnoreForeignKeyNames(): bool
    {
        return $this->ignoreForeignKeyNames;
    }

    /**
     * @param  bool  $ignoreForeignKeyNames
     */
    public function setIgnoreForeignKeyNames(bool $ignoreForeignKeyNames): void
    {
        $this->ignoreForeignKeyNames = $ignoreForeignKeyNames;
    }
}
