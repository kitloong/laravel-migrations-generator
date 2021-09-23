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
     * @param  Index[]  $indexes
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
     * Get a collection of single column indexes.
     *
     * @param  \Doctrine\DBAL\Schema\Index[]  $indexes
     * @return \Illuminate\Support\Collection<string, \Doctrine\DBAL\Schema\Index>
     */
    public function getSingleColumnIndexes(array $indexes): Collection
    {
        return (new Collection($indexes))
            ->filter(function (Index $index) {
                return count($index->getColumns()) === 1;
            })->keyBy(function (Index $index) {
                return $index->getColumns()[0];
            });
    }

    /**
     * Get a collection of composite indexes.
     *
     * @param  \Doctrine\DBAL\Schema\Index[]  $indexes
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
        } else {
            return IndexType::INDEX;
        }
    }

    /**
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
     * Doctrine/Dbal doesn't return spatial information from PostgreSQL
     * Use raw SQL here to create $spatial index name list.
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
}
