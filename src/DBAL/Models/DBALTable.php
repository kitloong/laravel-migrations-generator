<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models;

use Doctrine\DBAL\Schema\Column as DoctrineDBALColumn;
use Doctrine\DBAL\Schema\Index as DoctrineDBALIndex;
use Doctrine\DBAL\Schema\Table as DoctrineDBALTable;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\DBAL\Types\CustomType;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\CustomColumn;
use KitLoong\MigrationsGenerator\Schema\Models\Index;
use KitLoong\MigrationsGenerator\Schema\Models\Table;

abstract class DBALTable implements Table
{
    /**
     * @var string|null
     */
    protected $collation;

    /**
     * @var \Illuminate\Support\Collection<int, \KitLoong\MigrationsGenerator\Schema\Models\Column>
     */
    protected $columns;

    /**
     * @var \Illuminate\Support\Collection<int, \KitLoong\MigrationsGenerator\Schema\Models\CustomColumn>
     */
    protected $customColumns;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $comment;

    /**
     * @var \Illuminate\Support\Collection<int, \KitLoong\MigrationsGenerator\Schema\Models\Index>
     */
    protected $indexes;

    /**
     * Create a new instance.
     *
     * @param  array<string, \Doctrine\DBAL\Schema\Column>  $columns  Key is quoted name.
     * @param  array<string, \Doctrine\DBAL\Schema\Index>  $indexes  Key is name.
     */
    public function __construct(DoctrineDBALTable $table, array $columns, array $indexes)
    {
        $this->name      = $table->getName();
        $this->comment   = $table->getComment();
        $this->collation = $table->getOptions()['collation'] ?? null;

        $this->columns = (new Collection($columns))->reduce(function (Collection $columns, DoctrineDBALColumn $column) use ($table) {
            if (!$column->getType() instanceof CustomType) {
                $columns->push($this->makeColumn($table->getName(), $column));
            }

            return $columns;
        }, new Collection())->values();

        $this->customColumns = (new Collection($columns))->reduce(function (Collection $columns, DoctrineDBALColumn $column) use ($table) {
            if ($column->getType() instanceof CustomType) {
                $columns->push($this->makeCustomColumn($table->getName(), $column));
            }

            return $columns;
        }, new Collection())->values();

        $this->indexes = (new Collection($indexes))->map(function (DoctrineDBALIndex $index) use ($table) {
            return $this->makeIndex($table->getName(), $index);
        })->values();

        $this->handle();
    }

    /**
     * Instance extend this abstract may run special handling.
     */
    abstract protected function handle(): void;

    /**
     * Make a Column instance.
     */
    abstract protected function makeColumn(string $table, DoctrineDBALColumn $column): Column;

    /**
     * Make a CustomColumn instance.
     */
    abstract protected function makeCustomColumn(string $table, DoctrineDBALColumn $column): CustomColumn;

    /**
     * Make an Index instance.
     */
    abstract protected function makeIndex(string $table, DoctrineDBALIndex $index): Index;

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
