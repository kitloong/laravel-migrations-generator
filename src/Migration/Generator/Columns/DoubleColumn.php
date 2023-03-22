<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Columns;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnModifier;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;

class DoubleColumn implements ColumnTypeGenerator
{
    private const EMPTY_PRECISION = 0;
    private const EMPTY_SCALE     = 0;

    /**
     * @inheritDoc
     */
    public function generate(Table $table, Column $column): Method
    {
        $precisions = $this->getPrecisions($column);

        $method = new Method($column->getType(), $column->getName(), ...$precisions);

        if ($column->isUnsigned()) {
            $method->chain(ColumnModifier::UNSIGNED());
        }

        return $method;
    }

    /**
     * Get precision and scale.
     * Return empty if both precision and scale are 0.
     *
     * @return int[] "[]|[precision, scale]"
     */
    private function getPrecisions(Column $column): array
    {
        if (
            $column->getPrecision() === self::EMPTY_PRECISION
            && $column->getScale() === self::EMPTY_SCALE
        ) {
            return [];
        }

        return [$column->getPrecision(), $column->getScale()];
    }
}
