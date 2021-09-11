<?php

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Index;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod;
use KitLoong\MigrationsGenerator\MigrationMethod\IndexType;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository;

class IndexGenerator
{
    private $pgSQLRepository;
    private $sqlSrvRepository;

    public function __construct(PgSQLRepository $pgSQLRepository, SQLSrvRepository $sqlSrvRepository)
    {
        $this->pgSQLRepository  = $pgSQLRepository;
        $this->sqlSrvRepository = $sqlSrvRepository;
    }

    public function generate(Index $index): ColumnMethod
    {
        return new ColumnMethod($this->getIndexType($index), $index->getColumns());
    }

    /**
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
     * @param  \Doctrine\DBAL\Schema\Index[]  $indexes
     * @return \Illuminate\Support\Collection<\Doctrine\DBAL\Schema\Index>
     */
    public function getMultiColumnsIndexes(array $indexes): Collection
    {
        return (new Collection($indexes))
            ->filter(function (Index $index) {
                return count($index->getColumns()) > 1;
            });
    }

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
     * Doctrine/Dbal doesn't return spatial information from PostgreSQL
     * Use raw SQL here to create $spatial index name list.
     *
     * @param  string  $table
     * @return \Illuminate\Support\Collection Spatial index name list
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
