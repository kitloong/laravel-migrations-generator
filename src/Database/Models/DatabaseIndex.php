<?php

namespace KitLoong\MigrationsGenerator\Database\Models;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;
use KitLoong\MigrationsGenerator\Schema\Models\Index;

/**
 * @phpstan-import-type SchemaIndex from \KitLoong\MigrationsGenerator\Database\DatabaseSchema
 */
abstract class DatabaseIndex implements Index
{
    /**
     * @var string[]
     */
    protected array $columns;

    protected string $name;

    protected string $tableName;

    protected IndexType $type;

    /**
     * @var string[]
     */
    protected array $udtColumnSqls;

    /**
     * Create an index instance.
     *
     * @param  SchemaIndex  $index
     */
    public function __construct(string $table, array $index)
    {
        $this->tableName     = $table;
        $this->name          = $index['name'];
        $this->columns       = $index['columns'];
        $this->type          = $this->getIndexType($index);
        $this->udtColumnSqls = [];
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
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @inheritDoc
     */
    public function getType(): IndexType
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getUDTColumnSqls(): array
    {
        return $this->udtColumnSqls;
    }

    /**
     * @inheritDoc
     */
    public function hasUDTColumn(): bool
    {
        return count($this->udtColumnSqls) > 0;
    }

    /**
     * Get the index type.
     *
     * @param  SchemaIndex  $index
     */
    private function getIndexType(array $index): IndexType
    {
        if ($index['primary'] === true) {
            return IndexType::PRIMARY;
        }

        if ($index['unique'] === true) {
            return IndexType::UNIQUE;
        }

        // pgsql uses gist
        if ($index['type'] === 'spatial' || $index['type'] === 'gist') {
            return IndexType::SPATIAL_INDEX;
        }

        // pgsql uses gin
        if ($index['type'] === 'fulltext' || $index['type'] === 'gin') {
            return IndexType::FULLTEXT;
        }

        return IndexType::INDEX;
    }
}
