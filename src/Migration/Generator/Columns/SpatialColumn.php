<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Columns;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Support\CheckMigrationMethod;

class SpatialColumn implements ColumnTypeGenerator
{
    use CheckMigrationMethod;

    private const GEOMETRY_DEFAULT_SRID = 0;

    private const GEOGRAPHY_DEFAULT_SRID = 4326;

    /**
     * @inheritDoc
     */
    public function generate(Table $table, Column $column): Method
    {
        if (!$this->hasGeography()) {
            if ($column->getType() === ColumnType::GEOGRAPHY) {
                return new Method(ColumnType::GEOMETRY, $column->getName());
            }

            return new Method($column->getType(), $column->getName());
        }

        $methodValues = [$column->getName()];

        if ($column->getSpatialSubType() !== null) {
            $methodValues[] = $column->getSpatialSubType();
        }

        $srID = $this->getSrIDArg($column);

        if ($srID !== null) {
            if (count($methodValues) === 1) {
                $methodValues[] = null;
            }

            $methodValues[] = $srID;
        }

        return new Method($column->getType(), ...$methodValues);
    }

    /**
     * Get the SRID argument for spatial column.
     * Return null if the SRID is null or it matches the default SRID.
     */
    private function getSrIDArg(Column $column): ?int
    {
        if ($column->getSpatialSrID() === null) {
            return null;
        }

        switch ($column->getType()) {
            case ColumnType::GEOMETRY:
                if ($column->getSpatialSrID() !== self::GEOMETRY_DEFAULT_SRID) {
                    return $column->getSpatialSrID();
                }

                break;

            default:
                if ($column->getSpatialSrID() !== self::GEOGRAPHY_DEFAULT_SRID) {
                    return $column->getSpatialSrID();
                }
        }

        return null;
    }
}
