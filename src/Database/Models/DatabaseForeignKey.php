<?php

namespace KitLoong\MigrationsGenerator\Database\Models;

use KitLoong\MigrationsGenerator\Schema\Models\ForeignKey;

/**
 * @phpstan-import-type SchemaForeignKey from \KitLoong\MigrationsGenerator\Database\DatabaseSchema
 */
abstract class DatabaseForeignKey implements ForeignKey
{
    protected ?string $name;

    protected string $tableName;

    /**
     * @var string[]
     */
    protected array $localColumns;

    /**
     * @var string[]
     */
    protected array $foreignColumns;

    protected string $foreignTableName;

    protected ?string $onUpdate = null;

    protected ?string $onDelete = null;

    /**
     * @param  SchemaForeignKey  $foreignKey
     */
    public function __construct(string $table, array $foreignKey)
    {
        $this->tableName        = $table;
        $this->name             = $foreignKey['name'];
        $this->localColumns     = $foreignKey['columns'];
        $this->foreignColumns   = $foreignKey['foreign_columns'];
        $this->foreignTableName = $foreignKey['foreign_table'];
        $this->onUpdate         = $foreignKey['on_update'];
        $this->onDelete         = $foreignKey['on_delete'];
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?string
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
    public function getLocalColumns(): array
    {
        return $this->localColumns;
    }

    /**
     * @inheritDoc
     */
    public function getForeignColumns(): array
    {
        return $this->foreignColumns;
    }

    /**
     * @inheritDoc
     */
    public function getForeignTableName(): string
    {
        return $this->foreignTableName;
    }

    /**
     * @inheritDoc
     */
    public function getOnUpdate(): ?string
    {
        return $this->onUpdate;
    }

    /**
     * @inheritDoc
     */
    public function getOnDelete(): ?string
    {
        return $this->onDelete;
    }
}
