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

    /**
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Table  $table
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Index  $index
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Method
     */
    public function generate(Table $table, Index $index): Method
    {
        if ($this->indexNameHelper->shouldSkipName($table->getName(), $index)) {
            return new Method($index->getType(), $index->getColumns());
        }

        return new Method($index->getType(), $index->getColumns(), $index->getName());
    }

    /**
     * Get a list of chainable indexes.
     * Chainable indexes are index with single column, and able chained in migration.
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
            /** @var Collection<string, \KitLoong\MigrationsGenerator\Schema\Models\Index> $carry */
            if (count($index->getColumns()) > 1) {
                return $carry;
            }

            $columnName = $index->getColumns()[0];

            // Check if index is using framework style naming.
            // In old framework version "spatialIndex" modifier does not receive index name as argument.
            if (
                $index->getType()->equals(IndexType::SPATIAL_INDEX())
                && !$this->indexNameHelper->shouldSkipName($name, $index)
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
        return $indexes->filter(function (Index $index) use ($chainableIndexes) {
            // Composite index is not chainable.
            if (count($index->getColumns()) > 1) {
                return true;
            }

            // Single column primary will be handled by $chainableIndexes
            if ($index->getType()->equals(IndexType::PRIMARY())) {
                return false;
            }

            // Start from here, we need to handle single column index.
            $columnName = $index->getColumns()[0];

            // Set if the column is not set in $chainableIndexes
            if (!$chainableIndexes->has($columnName)) {
                return true;
            }

            /** @var \KitLoong\MigrationsGenerator\Schema\Models\Index $cIndex */
            $cIndex = $chainableIndexes->get($columnName);

            // $chainableIndexes contains list of indexes which chainable in the "column" migration.
            // A column may have multiple indexes., and we can only chain one index at a time.
            // If the same column has other indexes, we need to declare explicitly.
            // Hence, we keep only indexes not set in $chainableIndexes, by comparing the index name.
            return $cIndex->getName() !== $index->getName();
        });
    }
}
