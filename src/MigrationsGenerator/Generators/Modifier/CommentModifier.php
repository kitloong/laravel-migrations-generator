<?php

namespace MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use MigrationsGenerator\Generators\Blueprint\ColumnMethod;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;

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
