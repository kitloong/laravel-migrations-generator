<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Columns;

use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;

interface ColumnTypeGenerator
{
    /**
     * Generate the migration column method.
     */
    public function generate(Table $table, Column $column): Method;
}
