<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Columns;

use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;

class DecimalColumn implements ColumnTypeGenerator
{
    // Framework set (8, 2) as default precision.
    private const DEFAULT_PRECISION = 8;
    private const DEFAULT_SCALE     = 2;

    /**
     * @inheritDoc
     */
    public function generate(Table $table, Column $column): Method
    {
        $precisions = $this->getDecimalPrecisions($column->getPrecision(), $column->getScale());
        return new Method($column->getType(), $column->getName(), ...$precisions);
    }

    /**
     * Default decimal precision and scale is (8, 2).
     * Return precision and scale if the column is not (8, 2).
     *
     * @return int[] "[]|[precision]|[precision, scale]"
     */
    private function getDecimalPrecisions(int $precision, int $scale): array
    {
        if ($precision === self::DEFAULT_PRECISION && $scale === self::DEFAULT_SCALE) {
            return [];
        }

        if ($scale === self::DEFAULT_SCALE) {
            return [$precision];
        }

        return [$precision, $scale];
    }
}
