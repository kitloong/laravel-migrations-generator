<?php

namespace MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Collection;
use MigrationsGenerator\DBAL\Platform;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\IndexType;
use MigrationsGenerator\MigrationsGeneratorSetting;
use MigrationsGenerator\Repositories\PgSQLRepository;
use MigrationsGenerator\Repositories\SQLSrvRepository;

class IndexGenerator
{
    private $pgSQLRepository;
    private $sqlSrvRepository;

    public function __construct(PgSQLRepository $pgSQLRepository, SQLSrvRepository $sqlSrvRepository)
    {
        $this->pgSQLRepository  = $pgSQLRepository;
        $this->sqlSrvRepository = $sqlSrvRepository;
    }

    /**
     * Converts index into migration method.
     *
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\Index  $index
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function generate(Table $table, Index $index): Method
    {
        $indexType = $this->getIndexType($index);
        if ($this->shouldSkipName($table->getName(), $index, $indexType)) {
            return new Method($indexType, $index->getColumns());
        } else {
            return new Method($indexType, $index->getColumns(), $index->getName());
        }
    }

    /**
     * Checks and set `spatial` flag into Index if index is spatial index.
     *
     * @param  array<string, \Doctrine\DBAL\Schema\Index>  $indexes
     * @param  string  $table
     */
    public function setSpatialFlag(array $indexes, string $table): void
    {
        $spatialNames = $this->getSpatialList($table);
        foreach ($indexes as $index) {
            if ($spatialNames->contains($index->getName())) {
                $index->addFlag('spatial');
            }
        }
    }

    /**
     * Checks and set `fulltext` flag into Index if index is spatial index.
     *
     * @param  array<string, \Doctrine\DBAL\Schema\Index>  $indexes
     * @param  string  $table
     */
    public function setFulltextFlag(array $indexes, string $table): void
    {
        $fulltextList = $this->getFulltextList($table);
        foreach ($indexes as $index) {
            if ($fulltextList->contains($index->getName())) {
                $index->addFlag('fulltext');
            }
        }
    }

    /**
     * Get a collection of chainable indexes.
     * Chainable indexes wil be set in column generator.
     * eg: $table->string('date')->index();
     *
     * @param  string  $name  Table name
     * @param  array<string, \Doctrine\DBAL\Schema\Index>  $indexes
     * @return \Illuminate\Support\Collection<string, \Doctrine\DBAL\Schema\Index> Key is the column name.
     */
    public function getChainableIndexes(string $name, array $indexes): Collection
    {
        return (new Collection($indexes))
            ->reduce(function (Collection $carry, Index $index) use ($name) {
                /** @var Collection<string, \Doctrine\DBAL\Schema\Index> $carry */
                if (count($index->getColumns()) > 1) {
                    return $carry;
                }

                $columnName = $index->getUnquotedColumns()[0];

                $indexType = $this->getIndexType($index);

                // Only spatialIndex need extra filter.
                if ($indexType === IndexType::SPATIAL_INDEX) {
                    // Only spatialIndex with Laravel default naming is chainable.
                    if (!$this->shouldSkipName($name, $index, $indexType)) {
                        return $carry;
                    }
                }

                // Skip if $columnName isset, to maintain same order.
                if ($carry->has($columnName)) {
                    return $carry;
                }

                $carry->put($columnName, $index);
                return $carry;
            }, collect([]));
    }

    /**
     * Get a collection of not chainable indexes.
     * Not chainable indexes wil be set explicitly in the migration file.
     * eg:
     * $table->index(['col1', 'col2']);
     * $table->index(['col1', 'col2'], 'custom_name');
     *
     * @param  array<string, \Doctrine\DBAL\Schema\Index>  $indexes
     * @param  \Illuminate\Support\Collection<string, \Doctrine\DBAL\Schema\Index>  $chainableIndexes  Key is column name.
     * @return \Illuminate\Support\Collection<string, \Doctrine\DBAL\Schema\Index>  Key is index name.
     */
    public function getNotChainableIndexes(array $indexes, Collection $chainableIndexes): Collection
    {
        return (new Collection($indexes))
            ->filter(function (Index $index, string $indexName) use ($chainableIndexes) {
                // Composite index is not chainable.
                if (count($index->getColumns()) > 1) {
                    return true;
                }

                $columnName = $index->getUnquotedColumns()[0];

                // If $columnName is set in the $chainableIndexes.
                if ($chainableIndexes->has($columnName)) {
                    /** @var \Doctrine\DBAL\Schema\Index $cIndex */
                    $cIndex = $chainableIndexes->get($columnName);

                    // Primary is handled in ColumnGenerator.
                    if ($cIndex->isPrimary()) {
                        return false;
                    }

                    // Exclude current $cIndex by comparing $cIndex name with $indexName
                    // to remove duplication.
                    if ($cIndex->getName() === $indexName) {
                        return false;
                    }
                }

                return true;
            });
    }

    /**
     * Get a collection of composite indexes.
     *
     * @param  array<string, \Doctrine\DBAL\Schema\Index>  $indexes
     * @return \Illuminate\Support\Collection<\Doctrine\DBAL\Schema\Index>
     */
    public function getCompositeIndexes(array $indexes): Collection
    {
        return (new Collection($indexes))
            ->filter(function (Index $index) {
                return count($index->getColumns()) > 1;
            });
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Index  $index
     * @return string primary|unique|spatialIndex|index
     */
    public function getIndexType(Index $index): string
    {
        if ($index->isPrimary()) {
            return IndexType::PRIMARY;
        } elseif ($index->isUnique()) {
            return IndexType::UNIQUE;
        } elseif ($index->hasFlag('spatial')) {
            return IndexType::SPATIAL_INDEX;
        } elseif ($index->hasFlag('fulltext')) {
            return IndexType::FULLTEXT;
        } else {
            return IndexType::INDEX;
        }
    }

    /**
     * Skip generate index name in migration file:
     * 1. Index is primary.
     * 2. `--default-index-names` is true.
     * 3. Generator able create an index name, and that index name is the same with DB index name.
     *    Most of the time, this means the index is follow Laravel's default naming practice.
     *
     * @param  string  $table
     * @param  Index  $index
     * @param  string  $type
     * @return bool
     */
    public function shouldSkipName(string $table, Index $index, string $type): bool
    {
        if ($index->isPrimary()) {
            return true;
        }

        if (app(MigrationsGeneratorSetting::class)->isIgnoreIndexNames()) {
            return true;
        }

        $guessIndexName = strtolower($table.'_'.implode('_', $index->getColumns()).'_'.$type);
        $guessIndexName = str_replace(['-', '.'], '_', $guessIndexName);
        return $guessIndexName === $index->getName();
    }

    /**
     * Doctrine/Dbal doesn't return spatial information from PostgreSQL and SQL Server.
     * Use raw SQL here to create spatial index name list.
     *
     * @param  string  $table
     * @return \Illuminate\Support\Collection<string> Spatial index name list
     */
    private function getSpatialList(string $table): Collection
    {
        switch (app(MigrationsGeneratorSetting::class)->getPlatform()) {
            case Platform::POSTGRESQL:
                return $this->pgSQLRepository->getSpatialIndexNames($table);
            case Platform::SQLSERVER:
                return $this->sqlSrvRepository->getSpatialIndexNames($table);
            default:
                return new Collection();
        }
    }

    /**
     * Doctrine/Dbal doesn't return fulltext information from PostgreSQL
     * Use raw SQL here to create fulltext index name list.
     *
     * @param  string  $table
     * @return \Illuminate\Support\Collection<string> Spatial index name list
     */
    private function getFulltextList(string $table): Collection
    {
        switch (app(MigrationsGeneratorSetting::class)->getPlatform()) {
            case Platform::POSTGRESQL:
                return $this->pgSQLRepository->getFulltextIndexNames($table);
            default:
                return new Collection();
        }
    }
}
