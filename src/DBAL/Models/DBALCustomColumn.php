<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models;

use Doctrine\DBAL\Schema\Column as DoctrineDBALColumn;
use Doctrine\DBAL\Schema\TableDiff;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Schema\Models\CustomColumn;

abstract class DBALCustomColumn implements CustomColumn
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string[]
     */
    private $sqls;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __construct(string $table, DoctrineDBALColumn $column)
    {
        $this->name      = $column->getName();
        $this->tableName = $table;
        $this->sqls      = DB::getDoctrineSchemaManager()->getDatabasePlatform()->getAlterTableSQL(new TableDiff($this->tableName, [$column]));
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @inheritDoc
     */
    public function getSqls(): array
    {
        return $this->sqls;
    }
}
