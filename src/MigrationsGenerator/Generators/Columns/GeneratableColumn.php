<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\ColumnMethod;

interface GeneratableColumn
{
    public function generate(string $type, Table $table, Column $column): ColumnMethod;
}
