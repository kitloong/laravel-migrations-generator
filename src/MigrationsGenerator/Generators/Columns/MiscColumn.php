<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\ColumnMethod;

class MiscColumn implements GeneratableColumn
{
    public function generate(string $type, Table $table, Column $column): ColumnMethod
    {
        return new ColumnMethod($type, $column->getName());
    }
}
