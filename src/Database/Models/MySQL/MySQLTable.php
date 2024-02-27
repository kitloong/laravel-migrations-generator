<?php

namespace KitLoong\MigrationsGenerator\Database\Models\MySQL;

use KitLoong\MigrationsGenerator\Database\Models\DatabaseTable;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\CustomColumn;
use KitLoong\MigrationsGenerator\Schema\Models\Index;

class MySQLTable extends DatabaseTable
{
    /**
     * @inheritDoc
     */
    protected function makeColumn(string $table, array $column): Column
    {
        return new MySQLColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeCustomColumn(string $table, array $column): CustomColumn
    {
        return new MySQLCustomColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeIndex(string $table, array $index): Index
    {
        return new MySQLIndex($table, $index);
    }
}
