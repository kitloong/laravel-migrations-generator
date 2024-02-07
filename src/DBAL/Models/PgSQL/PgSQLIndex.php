<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\PgSQL;

use KitLoong\MigrationsGenerator\DBAL\Models\DBALIndex;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;
use KitLoong\MigrationsGenerator\Repositories\Entities\PgSQL\IndexDefinition;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Support\CheckMigrationMethod;

class PgSQLIndex extends DBALIndex
{
    use CheckMigrationMethod;

    private PgSQLRepository $repository;

    protected function handle(): void
    {
        $this->repository = app(PgSQLRepository::class);

        $this->setTypeToSpatial();

        switch ($this->type) {
            case IndexType::PRIMARY():
                // Reset name to empty to indicate use the database platform naming.
                $this->name = '';
                break;

            default:
        }
    }

    private function setTypeToSpatial(): void
    {
        $spatialNames = $this->repository->getSpatialIndexes($this->tableName)
            ->map(static fn (IndexDefinition $indexDefinition) => $indexDefinition->getIndexName());

        if (!$spatialNames->contains($this->name)) {
            return;
        }

        $this->type = IndexType::SPATIAL_INDEX();
    }
}
