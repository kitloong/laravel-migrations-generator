<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\SQLSrv;

use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALIndex;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;
use KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository;
use KitLoong\MigrationsGenerator\Support\Regex;

class SQLSrvIndex extends DBALIndex
{
    /**
     * @var \KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository
     */
    private $repository;

    protected function handle(): void
    {
        $this->repository = app(SQLSrvRepository::class);

        switch ($this->type) {
            case IndexType::PRIMARY():
                $this->resetPrimaryNameToEmptyIfIsDefaultName();
                break;

            default:
                $this->changeTypeToSpatial();
        }
    }

    /**
     * Change the index type to `spatial` if the name is in the spatial index name list.
     */
    private function changeTypeToSpatial(): void
    {
        $spatialNames = $this->repository->getSpatialIndexNames($this->tableName);

        if (!$spatialNames->contains($this->name)) {
            return;
        }

        $this->type = IndexType::SPATIAL_INDEX();
    }

    /**
     * Reset primary index name to empty if the name is using default naming convention.
     *
     * @see https://learnsql.com/cookbook/what-is-the-default-constraint-name-in-sql-server/ for default naming convention.
     */
    private function resetPrimaryNameToEmptyIfIsDefaultName(): void
    {
        $prefix = 'PK__' . Str::substr($this->tableName, 0, 8) . '__';

        // Can be improved by generate exact 16 characters of sequence number instead of `\w{16}`
        // if the rules of sequence number generation is known.
        if ($this->name !== Regex::match('/' . $prefix . '\w{16}/', $this->name)) {
            return;
        }

        $this->name = '';
    }
}
