<?php

namespace MigrationsGenerator;

use Carbon\Carbon;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use MigrationsGenerator\DBAL\Platform;

class MigrationsGeneratorSetting
{
    /** @var Connection */
    private $connection;

    /** @var AbstractPlatform */
    private $databasePlatform;

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
    private $viewFilename;

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
    public function setup(string $connection): void
    {
        $this->connection = DB::connection($connection);

        $doctrineConnection = $this->connection->getDoctrineConnection();
        if (method_exists($doctrineConnection, 'createSchemaManager')) {
            $this->schema = $doctrineConnection->createSchemaManager();
        } else {
            // @codeCoverageIgnoreStart
            $this->schema = $doctrineConnection->getSchemaManager();
            // @codeCoverageIgnoreEnd
        }
        $this->databasePlatform = $doctrineConnection->getDatabasePlatform();

        switch ($this->databasePlatform->getName()) {
            case 'mysql':
                $this->platform = Platform::MYSQL;
                break;
            case 'postgresql':
                $this->platform = Platform::POSTGRESQL;
                break;
            case 'mssql':
                $this->platform = Platform::SQLSERVER;
                break;
            // @codeCoverageIgnoreStart
            case 'sqlite':
                $this->platform = Platform::SQLITE;
                break;
            default:
                $this->platform = Platform::OTHERS;
            // @codeCoverageIgnoreEnd
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
        $this->stubPath = $stubPath;
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
    public function getViewFilename(): string
    {
        return $this->viewFilename;
    }

    /**
     * @param  string  $viewFilename
     */
    public function setViewFilename(string $viewFilename): void
    {
        $this->viewFilename = $viewFilename;
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
