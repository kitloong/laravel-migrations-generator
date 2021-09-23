<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;

class DecimalColumn implements GeneratableColumn
{
    // (8, 2) are default value of decimal, float
    private const DECIMAL_DEFAULT_PRECISION = 8;
    private const DECIMAL_DEFAULT_SCALE     = 2;

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
        return $this->getDecimalPrecisions($column->getPrecision(), $column->getScale());
    }

    /**
     * Default decimal precision and scale is (8, 2).
     * Return precision and scale if this column is not (8, 2).
     *
     * @param  int  $precision
     * @param  int  $scale
     * @return int[] [precision, scale]
     */
    private function getDecimalPrecisions(int $precision, int $scale): array
    {
        if ($precision === self::DECIMAL_DEFAULT_PRECISION && $scale === self::DECIMAL_DEFAULT_SCALE) {
            return [];
        }

        if ($scale === self::DECIMAL_DEFAULT_SCALE) {
            return [$precision];
        }

        return [$precision, $scale];
    }
}
