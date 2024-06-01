<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Columns;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnModifier;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Support\CheckLaravelVersion;

class FloatColumn implements ColumnTypeGenerator
{
    use CheckLaravelVersion;

    // Laravel version before 11 set (8, 2) as default precision.
    private const DEFAULT_PRECISION = 8;
    private const DEFAULT_SCALE     = 2;

    private const DEFAULT_PRECISION_V11 = 53;

    /**
     * @inheritDoc
     */
    public function generate(Table $table, Column $column): Method
    {
        $precisions = $this->getPrecisions($column);

        $method = new Method($column->getType(), $column->getName(), ...$precisions);

        if ($column->isUnsigned()) {
            $method->chain(ColumnModifier::UNSIGNED);
        }

        return $method;
    }

    /**
     * Get precision and scale.
     * Return empty if precision = 8 and scale = 2.
     *
     * @return array<int, int|null> "[]|[precision]|[precision, scale]"
     */
    private function getPrecisions(Column $column): array
    {
        if ($this->atLeastLaravel11()) {
            if ($column->getPrecision() === null || $column->getPrecision() === self::DEFAULT_PRECISION_V11) {
                return [];
            }

            return [$column->getPrecision()];
        }

        if (
            $column->getPrecision() === self::DEFAULT_PRECISION
            && $column->getScale() === self::DEFAULT_SCALE
        ) {
            return [];
        }

        if ($column->getScale() === self::DEFAULT_SCALE) {
            return [$column->getPrecision()];
        }

        return [$column->getPrecision(), $column->getScale()];
    }
}
