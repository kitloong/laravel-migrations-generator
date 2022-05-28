<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Modifiers;

use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;

interface Modifier
{
    /**
     * Chain column modifier.
     *
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Method  $method
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Table  $table
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Column  $column
     * @param  mixed  ...$args
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Method
     */
    public function chain(Method $method, Table $table, Column $column, ...$args): Method;
}
