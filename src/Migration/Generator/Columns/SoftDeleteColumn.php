<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Columns;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnModifier;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;

class SoftDeleteColumn implements ColumnTypeGenerator
{
    private const DEFAULT_PRECISION = 0;

    /**
     * @inheritDoc
     */
    public function generate(Table $table, Column $column): Method
    {
        $method = $this->makeMethod($column);

        if ($column->isOnUpdateCurrentTimestamp()) {
            $method->chain(ColumnModifier::USE_CURRENT_ON_UPDATE());
        }

        return $method;
    }

    /**
     * Create a Method instance.
     */
    private function makeMethod(Column $column): Method
    {
        $length = $column->getLength() === self::DEFAULT_PRECISION ? null : $column->getLength();

        if ($length !== null) {
            return new Method($column->getType(), $column->getName(), $length);
        }

        return new Method($column->getType());
    }
}
