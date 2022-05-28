<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Columns;

use Illuminate\Database\Schema\Builder;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;

class StringColumn implements ColumnTypeGenerator
{
    /**
     * @inheritDoc
     */
    public function generate(Table $table, Column $column): Method
    {
        if ($column->getLength() !== null && $column->getLength() !== Builder::$defaultStringLength) {
            return new Method($column->getType(), $column->getName(), $column->getLength());
        }

        return new Method($column->getType(), $column->getName());
    }
}
