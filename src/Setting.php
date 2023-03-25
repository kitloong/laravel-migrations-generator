<?php

namespace KitLoong\MigrationsGenerator;

use Carbon\Carbon;

class Setting
{
    /**
     * The default DB connection name, also known as "previous" connection name if migration is called
     * with `--connection=other` option.
     *
     * @var string
     */
    private $defaultConnection;

    /** @var bool */
    private $useDBCollation;

    /** @var bool */
    private $ignoreIndexNames;

    /** @var bool */
    private $ignoreForeignKeyNames;

    /** @var bool */
    private $squash;

    /** @var string */
    private $path;

    /** @var string */
    private $stubPath;

    /** @var \Carbon\Carbon */
    private $date;

    /** @var string */
    private $tableFilename;

    /** @var string */
    private $viewFilename;

    /** @var string */
    private $procedureFilename;

    /** @var string */
    private $fkFilename;

    /** @var bool */
    private $withHasTable;

    public function getDefaultConnection(): string
    {
        return $this->defaultConnection;
    }

    public function setDefaultConnection(string $defaultConnection): void
    {
        $this->defaultConnection = $defaultConnection;
    }

    public function isUseDBCollation(): bool
    {
        return $this->useDBCollation;
    }

    public function setUseDBCollation(bool $useDBCollation): void
    {
        $this->useDBCollation = $useDBCollation;
    }

    public function isIgnoreIndexNames(): bool
    {
        return $this->ignoreIndexNames;
    }

    public function setIgnoreIndexNames(bool $ignoreIndexNames): void
    {
        $this->ignoreIndexNames = $ignoreIndexNames;
    }

    public function isIgnoreForeignKeyNames(): bool
    {
        return $this->ignoreForeignKeyNames;
    }

    public function setIgnoreForeignKeyNames(bool $ignoreForeignKeyNames): void
    {
        $this->ignoreForeignKeyNames = $ignoreForeignKeyNames;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getStubPath(): string
    {
        return $this->stubPath;
    }

    public function setStubPath(string $stubPath): void
    {
        $this->stubPath = $stubPath;
    }

    public function isSquash(): bool
    {
        return $this->squash;
    }

    public function setSquash(bool $squash): void
    {
        $this->squash = $squash;
    }

    public function getTableFilename(): string
    {
        return $this->tableFilename;
    }

    public function setTableFilename(string $tableFilename): void
    {
        $this->tableFilename = $tableFilename;
    }

    public function getViewFilename(): string
    {
        return $this->viewFilename;
    }

    public function setViewFilename(string $viewFilename): void
    {
        $this->viewFilename = $viewFilename;
    }

    public function getProcedureFilename(): string
    {
        return $this->procedureFilename;
    }

    public function setProcedureFilename(string $procedureFilename): void
    {
        $this->procedureFilename = $procedureFilename;
    }

    public function getFkFilename(): string
    {
        return $this->fkFilename;
    }

    public function setFkFilename(string $fkFilename): void
    {
        $this->fkFilename = $fkFilename;
    }

    public function getDate(): Carbon
    {
        return $this->date;
    }

    public function getDateForMigrationFilename(): string
    {
        return $this->date->format('Y_m_d_His');
    }

    public function setDate(Carbon $date): void
    {
        $this->date = $date;
    }

    public function isWithHasTable(): bool
    {
        return $this->withHasTable;
    }

    public function setWithHasTable(bool $withHasTable): void
    {
        $this->withHasTable = $withHasTable;
    }
}
