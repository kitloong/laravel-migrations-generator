<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 22:55
 */

namespace KitLoong\MigrationsGenerator;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Generators\Platform;

class MigrationGeneratorSetting
{
    /**
     * @var string
     */
    private $connection;

    /**
     * @var \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    private $databasePlatform;

    /**
     * @var string
     */
    private $platform;

    /**
     * @return string
     */
    public function getConnection(): string
    {
        return $this->connection;
    }

    /**
     * @param  string  $connection
     */
    public function setConnection(string $connection): void
    {
        $this->connection = $connection;

        /** @var \Doctrine\DBAL\Connection $doctConn */
        $doctConn = DB::connection($this->connection)->getDoctrineConnection();
        $this->databasePlatform = $doctConn->getDatabasePlatform();
        $classPath = explode('\\', get_class($this->databasePlatform));
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
     * @return AbstractPlatform
     */
    public function getDatabasePlatform(): AbstractPlatform
    {
        return $this->databasePlatform;
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }
}
