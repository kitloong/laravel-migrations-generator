<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\Method;

interface GeneratableColumn
{
    /**
     * Generates migration column method.
     *
     * @param  string  $type  Column type name.
     * @param  Table  $table
     * @param  Column  $column
     * @return Method  Generated column method.
     */
    public function generate(string $type, Table $table, Column $column): Method;
}
