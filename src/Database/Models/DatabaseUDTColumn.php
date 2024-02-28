<?php

namespace KitLoong\MigrationsGenerator\Database\Models;

use KitLoong\MigrationsGenerator\Schema\Models\UDTColumn;

/**
 * @phpstan-import-type SchemaColumn from \KitLoong\MigrationsGenerator\Database\DatabaseSchema
 */
abstract class DatabaseUDTColumn implements UDTColumn
{
    protected string $name;

    protected string $tableName;

    /**
     * @var string[]
     */
    protected array $sqls;

    /**
     * @param  SchemaColumn  $column
     */
    public function __construct(string $table, array $column)
    {
        $this->name      = $column['name'];
        $this->tableName = $table;
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
