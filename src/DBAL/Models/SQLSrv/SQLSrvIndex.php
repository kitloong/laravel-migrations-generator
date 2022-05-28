<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\SQLSrv;

use KitLoong\MigrationsGenerator\DBAL\Models\DBALIndex;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;
use KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository;

class SQLSrvIndex extends DBALIndex
{
    /**
     * @var \KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository
     */
    private $repository;

    protected function handle(): void
    {
        $this->repository = app(SQLSrvRepository::class);

        $this->setTypeToSpatial();
    }

    private function setTypeToSpatial(): void
    {
        $spatialNames = $this->repository->getSpatialIndexNames($this->tableName);
        if ($spatialNames->contains($this->name)) {
            $this->type = IndexType::SPATIAL_INDEX();
        }
    }
}
