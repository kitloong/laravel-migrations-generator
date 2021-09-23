<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\DBAL\Platform;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use MigrationsGenerator\MigrationsGeneratorSetting;

class FloatColumn implements GeneratableColumn
{
    // (8, 2) are default value of float
    private const MIGRATION_FLOAT_DEFAULT_PRECISION = 8;
    private const MIGRATION_FLOAT_DEFAULT_SCALE     = 2;

    private const PGSQL_FLOAT_EMPTY_PRECISION = 10;
    private const PGSQL_FLOAT_EMPTY_SCALE     = 0;

    private const SQLSRV_FLOAT_EMPTY_PRECISION = 53;
    private const SQLSRV_FLOAT_EMPTY_SCALE     = 0;

    public function generate(string $type, Table $table, Column $column): Method
    {
        $precisions = $this->getPrecisions($column);

        $method = new Method($type, $column->getName(), ...$precisions);

        if ($column->getUnsigned()) {
            $method->chain(ColumnModifier::UNSIGNED);
        }

        return $method;
    }

    /**
     * Get float precision.
     *
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return int[] [precision, scale]
     */
    private function getPrecisions(Column $column): array
    {
        switch (app(MigrationsGeneratorSetting::class)->getPlatform()) {
            case Platform::POSTGRESQL:
                return $this->getPgSQLFloatPrecisions($column->getPrecision(), $column->getScale());
            case Platform::SQLSERVER:
                return $this->getSQLSrvFloatPrecisions($column->getPrecision(), $column->getScale());
            default:
                return $this->getFloatPrecisions($column->getPrecision(), $column->getScale());
        }
    }

    /**
     * Get float precision for SQLSrv.
     *
     * @param  int  $precision
     * @param  int  $scale
     * @return int[] [precision, scale]
     */
    private function getSQLSrvFloatPrecisions(int $precision, int $scale): array
    {
        if ($precision === self::SQLSRV_FLOAT_EMPTY_PRECISION && $scale === self::SQLSRV_FLOAT_EMPTY_SCALE) {
            return [];
        }

        return $this->getFloatPrecisions($precision, $scale);
    }

    /**
     * Get float precision for PgSQL.
     *
     * @param  int  $precision
     * @param  int  $scale
     * @return int[] [precision, scale]
     */
    private function getPgSQLFloatPrecisions(int $precision, int $scale): array
    {
        if ($precision === self::PGSQL_FLOAT_EMPTY_PRECISION && $scale === self::PGSQL_FLOAT_EMPTY_SCALE) {
            return [];
        }

        return $this->getFloatPrecisions($precision, $scale);
    }

    /**
     * Get float precision.
     *
     * @param  int  $precision
     * @param  int  $scale
     * @return int[] [precision, scale]
     */
    private function getFloatPrecisions(int $precision, int $scale): array
    {
        if ($precision === self::MIGRATION_FLOAT_DEFAULT_PRECISION && $scale === self::MIGRATION_FLOAT_DEFAULT_SCALE) {
            return [];
        }

        if ($scale === self::MIGRATION_FLOAT_DEFAULT_SCALE) {
            return [$precision];
        }

        return [$precision, $scale];
    }
}
