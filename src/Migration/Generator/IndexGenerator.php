<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator;

use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Index;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Support\IndexNameHelper;

class IndexGenerator
{
    private $indexNameHelper;

    public function __construct(IndexNameHelper $indexNameHelper)
    {
        $this->indexNameHelper = $indexNameHelper;
    }

    public function generate(Table $table, Index $index): Method
    {
        $columns = $this->getColumns($index);

        if ($this->indexNameHelper->shouldSkipName($table->getName(), $index)) {
            return new Method($index->getType(), $columns);
        }

        return new Method($index->getType(), $columns, $index->getName());
    }

    /**
     * Get a list of chainable indexes.
     * Chainable indexes are index with single column, and able be chained in column declaration.
     * eg:
     * $table->string('email')->index('chainable_index');
     * $table->integer('id')->primary();
     *
     * @param  string  $name  Table name
     * @param  \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\Index>  $indexes
     * @return \Illuminate\Support\Collection<string, \KitLoong\MigrationsGenerator\Schema\Models\Index> Key is the column name.
     */
    public function getChainableIndexes(string $name, Collection $indexes): Collection
    {
        return $indexes->reduce(function (Collection $carry, Index $index) use ($name) {
            /** @var \Illuminate\Support\Collection<string, \KitLoong\MigrationsGenerator\Schema\Models\Index> $carry */
            if (count($index->getColumns()) > 1) {
                return $carry;
            }

            // TODO Laravel 10 does not support `$table->index(DB::raw("with_length(16)"))`
//            if ($index->getLengths()[0] !== null) {
//                return $carry;
//            }

            $columnName = $index->getColumns()[0];

            // Check if index is using framework default naming.
            // The older version "spatialIndex" modifier does not accept index name as argument.
            if (
                $index->getType()->equals(IndexType::SPATIAL_INDEX())
                && !$this->indexNameHelper->shouldSkipName($name, $index)
            ) {
                return $carry;
            }

            // If name is not empty, primary name should be set explicitly.
            if (
                $index->getType()->equals(IndexType::PRIMARY())
                && $index->getName() !== ''
            ) {
                return $carry;
            }

            // A column may have multiple indexes.
            // Set only first found index as chainable.
            if ($carry->has($columnName)) {
                return $carry;
            }

            $carry->put($columnName, $index);
            return $carry;
        }, new Collection());
    }

    /**
     * Get a list of not chainable indexes.
     * Not chainable indexes are index with multi columns, or other indexes that must be explicitly defined in migration.
     * eg:
     * $table->index(['col1', 'col2'], 'not_chainable_index');
     * $table->integer(['col1', 'col2'])->primary();
     *
     * @param  \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\Index>  $indexes
     * @param  \Illuminate\Support\Collection<string, \KitLoong\MigrationsGenerator\Schema\Models\Index>  $chainableIndexes  Key is column name.
     * @return \Illuminate\Support\Collection<string, \KitLoong\MigrationsGenerator\Schema\Models\Index>  Key is index name.
     */
    public function getNotChainableIndexes(Collection $indexes, Collection $chainableIndexes): Collection
    {
        $chainableNames = $chainableIndexes->map(function (Index $index) {
            return $index->getName();
        });

        return $indexes->filter(function (Index $index) use ($chainableNames) {
            return !$chainableNames->contains($index->getName());
        });
    }

    /**
     * Get column names with length.
     *
     * @return array<string|\Illuminate\Database\Query\Expression>
     */
    private function getColumns(Index $index): array
    {
        $cols = [];

        foreach ($index->getColumns() as $column) {
            // TODO Laravel 10 does not support `$table->index(DB::raw("with_length(16)"))`
//            if ($index->getLengths()[$i] !== null) {
//                $cols[] = DB::raw($column . '(' . $index->getLengths()[$i] . ')');
//                continue;
//            }
            $cols[] = $column;
        }

        return $cols;
    }
}
