<?php

namespace MigrationsGenerator;

use Carbon\Carbon;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use MigrationsGenerator\DBAL\Platform;

class MigrationsGeneratorSetting
{
    /** @var Connection */
    private $connection;

    /** @var string */
    private $platform;

    /** @var AbstractSchemaManager */
    private $schema;

    /** @var boolean */
    private $useDBCollation;

    /** @var boolean */
    private $ignoreIndexNames;

    /** @var boolean */
    private $ignoreForeignKeyNames;

    /** @var boolean */
    private $squash;

    /** @var string */
    private $path;

    /** @var string */
    private $stubPath;

    /** @var Carbon */
    private $date;

    /** @var string */
    private $tableFilename;

    /** @var string */
    private $fkFilename;

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param  string  $connection
     * @throws \Doctrine\DBAL\Exception
     */
    public function setConnection(string $connection): void
    {
        $this->connection = DB::connection($connection);

        $doctConn     = $this->connection->getDoctrineConnection();
        $this->schema = $doctConn->getSchemaManager();
        $classPath    = explode('\\', get_class($doctConn->getDatabasePlatform()));
        $platform     = end($classPath);

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
    public function isUseDBCollation(): bool
    {
        return $this->useDBCollation;
    }

    /**
     * @param  bool  $useDBCollation
     */
    public function setUseDBCollation(bool $useDBCollation): void
    {
        $this->useDBCollation = $useDBCollation;
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

    /**
     * @return AbstractSchemaManager
     */
    public function getSchema(): AbstractSchemaManager
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param  string  $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getStubPath(): string
    {
        return $this->stubPath;
    }

    /**
     * @param  string  $stubPath
     */
    public function setStubPath(string $stubPath): void
    {
        // Use user defined stub path.
        if ($stubPath !== Config::get('generators.config.migration_template_path')) {
            $this->stubPath = $stubPath;
            return;
        }

        // Use default stub path.
        $this->stubPath = Config::get('generators.config.migration_template_path');
    }

    /**
     * @return bool
     */
    public function isSquash(): bool
    {
        return $this->squash;
    }

    /**
     * @param  bool  $squash
     */
    public function setSquash(bool $squash): void
    {
        $this->squash = $squash;
    }

    /**
     * @return string
     */
    public function getTableFilename(): string
    {
        return $this->tableFilename;
    }

    /**
     * @param  string  $tableFilename
     */
    public function setTableFilename(string $tableFilename): void
    {
        $this->tableFilename = $tableFilename;
    }

    /**
     * @return string
     */
    public function getFkFilename(): string
    {
        return $this->fkFilename;
    }

    /**
     * @param  string  $fkFilename
     */
    public function setFkFilename(string $fkFilename): void
    {
        $this->fkFilename = $fkFilename;
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /**
     * @param  Carbon  $date
     */
    public function setDate(Carbon $date): void
    {
        $this->date = $date;
    }
}
