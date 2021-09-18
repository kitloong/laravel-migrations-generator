<?php

namespace KitLoong\MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod;
use KitLoong\MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;

class CommentModifier
{
    public function chainComment(ColumnMethod $method, string $type, Column $column): ColumnMethod
    {
        if ($column->getComment() !== null) {
            $method->chain(ColumnModifier::COMMENT, $column->getComment());
        }
        return $method;
    }
}
