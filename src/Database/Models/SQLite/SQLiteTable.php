<?php

namespace KitLoong\MigrationsGenerator\Database\Models\SQLite;

use KitLoong\MigrationsGenerator\Database\Models\DatabaseTable;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\CustomColumn;
use KitLoong\MigrationsGenerator\Schema\Models\Index;

class SQLiteTable extends DatabaseTable
{
    /**
     * @inheritDoc
     */
    protected function makeColumn(string $table, array $column): Column
    {
        return new SQLiteColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeCustomColumn(string $table, array $column): CustomColumn
    {
        return new SQLiteCustomColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeIndex(string $table, array $index): Index
    {
        return new SQLiteIndex($table, $index);
    }
}
