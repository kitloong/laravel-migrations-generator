<?php

namespace MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;

class CommentModifier
{
    /**
     * Set comment.
     *
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function chainComment(Method $method, Column $column): Method
    {
        if ($column->getComment() !== null) {
            $method->chain(ColumnModifier::COMMENT, $column->getComment());
        }
        return $method;
    }
}
