<?php

namespace KitLoong\MigrationsGenerator\Database\Models;

use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\CustomColumn;
use KitLoong\MigrationsGenerator\Schema\Models\Index;
use KitLoong\MigrationsGenerator\Schema\Models\Table;

/**
 * @phpstan-import-type SchemaColumn from \KitLoong\MigrationsGenerator\Database\DatabaseSchema
 * @phpstan-import-type SchemaTable from \KitLoong\MigrationsGenerator\Database\DatabaseSchema
 * @phpstan-import-type SchemaIndex from \KitLoong\MigrationsGenerator\Database\DatabaseSchema
 */
abstract class DatabaseTable implements Table
{
    protected ?string $collation = null;

    /**
     * @var \Illuminate\Support\Collection<int, \KitLoong\MigrationsGenerator\Schema\Models\Column>
     */
    protected Collection $columns;

    /**
     * @var \Illuminate\Support\Collection<int, \KitLoong\MigrationsGenerator\Schema\Models\CustomColumn>
     */
    protected Collection $customColumns;

    protected string $name;

    protected ?string $comment = null;

    /**
     * @var \Illuminate\Support\Collection<int, \KitLoong\MigrationsGenerator\Schema\Models\Index>
     */
    protected Collection $indexes;

    /**
     * Make a Column instance.
     *
     * @param  SchemaColumn  $column
     */
    abstract protected function makeColumn(string $table, array $column): Column;

    /**
     * Make a CustomColumn instance.
     *
     * @param  SchemaColumn  $column
     */
    abstract protected function makeCustomColumn(string $table, array $column): CustomColumn;

    /**
     * Make an Index instance.
     *
     * @param  SchemaIndex  $index
     */
    abstract protected function makeIndex(string $table, array $index): Index;

    /**
     * Create a new instance.
     *
     * @param  SchemaTable  $table
     * @param  \Illuminate\Support\Collection<int, SchemaColumn>  $columns  Key is quoted name.
     * @param  \Illuminate\Support\Collection<int, SchemaIndex>  $indexes  Key is name.
     * @param  \Illuminate\Support\Collection<int, string>  $userDefinedTypes
     */
    public function __construct(array $table, Collection $columns, Collection $indexes, Collection $userDefinedTypes)
    {
        $this->name      = $table['name'];
        $this->comment   = $table['comment'];
        $this->collation = $table['collation'];

        $this->columns = $columns->reduce(function (Collection $columns, array $column) use ($userDefinedTypes) {
            if (!$userDefinedTypes->contains($column['type_name'])) {
                $columns->push($this->makeColumn($this->name, $column));
            }

            return $columns;
        }, new Collection())->values();

        $this->customColumns = $columns->reduce(function (Collection $columns, array $column) use ($userDefinedTypes) {
            if ($userDefinedTypes->contains($column['type_name'])) {
                $columns->push($this->makeCustomColumn($this->name, $column));
            }

            return $columns;
        }, new Collection())->values();

        $this->indexes = $indexes->map(fn (array $index) => $this->makeIndex($this->name, $index))->values();
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
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @inheritDoc
     */
    public function getColumns(): Collection
    {
        return $this->columns;
    }

    /**
     * @inheritDoc
     */
    public function getCustomColumns(): Collection
    {
        return $this->customColumns;
    }

    /**
     * @inheritDoc
     */
    public function getIndexes(): Collection
    {
        return $this->indexes;
    }

    /**
     * @inheritDoc
     */
    public function getCollation(): ?string
    {
        return $this->collation;
    }
}
