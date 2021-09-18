<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\Method;

interface GeneratableColumn
{
    public function generate(string $type, Table $table, Column $column): Method;
}
