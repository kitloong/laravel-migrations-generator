<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Modifiers;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnModifier;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;

class CommentModifier implements Modifier
{
    /**
     * @inheritDoc
     */
    public function chain(Method $method, Table $table, Column $column, mixed ...$args): Method
    {
        if ($column->getComment() !== null) {
            $method->chain(ColumnModifier::COMMENT, $column->getComment());
        }

        return $method;
    }
}
