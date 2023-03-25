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
     * @param  mixed  ...$args
     */
    public function chain(Method $method, Table $table, Column $column, ...$args): Method;
}
