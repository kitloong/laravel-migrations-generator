<?php

namespace KitLoong\MigrationsGenerator\Database\Models\SQLSrv;

use KitLoong\MigrationsGenerator\Database\Models\DatabaseTable;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\CustomColumn;
use KitLoong\MigrationsGenerator\Schema\Models\Index;

class SQLSrvTable extends DatabaseTable
{
    /**
     * @inheritDoc
     */
    protected function makeColumn(string $table, array $column): Column
    {
        return new SQLSrvColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeCustomColumn(string $table, array $column): CustomColumn
    {
        return new SQLSrvCustomColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeIndex(string $table, array $index): Index
    {
        return new SQLSrvIndex($table, $index);
    }
}
