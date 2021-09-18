<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\DBAL\Platform;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;
use MigrationsGenerator\MigrationsGeneratorSetting;

class DecimalColumn implements GeneratableColumn
{
    // (8, 2) are default value of decimal, float
    private const DEFAULT_DECIMAL_PRECISION = 8;
    private const DEFAULT_DECIMAL_SCALE     = 2;

    // DBAL return (10, 0) if double length is empty
    private const EMPTY_PRECISION = 10;
    private const EMPTY_SCALE     = 0;

    public function generate(string $type, Table $table, Column $column): Method
    {
        $precisions = $this->getPrecisions($type, $column);

        $method = new Method($type, $column->getName(), ...$precisions);

        if ($column->getUnsigned()) {
            $method->chain(ColumnModifier::UNSIGNED);
        }

        return $method;
    }

    private function getPrecisions(string $type, Column $column): array
    {
        switch (app(MigrationsGeneratorSetting::class)->getPlatform()) {
            case Platform::POSTGRESQL:
                if ($type === ColumnType::DECIMAL) {
                    return $this->getDecimalPrecisions($column->getPrecision(), $column->getScale());
                }
                break;
            default:
        }

        if (in_array($type, [ColumnType::DECIMAL, ColumnType::FLOAT])) {
            return $this->getDecimalPrecisions($column->getPrecision(), $column->getScale());
        }
        return $this->getDoublePrecisions($column->getPrecision(), $column->getScale());
    }

    /**
     * Default decimal precision and scale is (8, 2)
     * Return precision and scale if this column is not (8, 2)
     *
     * @param  int  $precision
     * @param  int  $scale
     * @return int[] [precision, scale]
     */
    private function getDecimalPrecisions(int $precision, int $scale): array
    {
        if ($precision === self::DEFAULT_DECIMAL_PRECISION && $scale === self::DEFAULT_DECIMAL_SCALE) {
            return [];
        }

        if ($scale === self::DEFAULT_DECIMAL_SCALE) {
            return [$precision];
        }

        return [$precision, $scale];
    }

    /**
     * Default decimal precision and scale is (10, 0)
     * Return precision and scale if this column is not (10, 0)
     *
     * @param  int  $precision
     * @param  int  $scale
     * @return int[]
     */
    private function getDoublePrecisions(int $precision, int $scale): array
    {
        if ($precision === self::EMPTY_PRECISION && $scale === self::EMPTY_SCALE) {
            return [];
        }

        return [$precision, $scale];
    }
}
