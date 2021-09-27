<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;

class DoubleColumn implements GeneratableColumn
{
    // DBAL return (10, 0) if double length is empty
    private const DOUBLE_EMPTY_PRECISION = 10;
    private const DOUBLE_EMPTY_SCALE     = 0;

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
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return int[] [precision, scale]
     */
    private function getPrecisions(Column $column): array
    {
        return $this->getDoublePrecisions($column->getPrecision(), $column->getScale());
    }

    /**
     * Empty double precision and scale is (10, 0).
     * Return precision and scale if this column is not (10, 0).
     *
     * @param  int  $precision
     * @param  int  $scale
     * @return int[] [precision, scale]
     */
    private function getDoublePrecisions(int $precision, int $scale): array
    {
        if ($precision === self::DOUBLE_EMPTY_PRECISION && $scale === self::DOUBLE_EMPTY_SCALE) {
            return [];
        }

        return [$precision, $scale];
    }
}
