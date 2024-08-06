<?php

namespace KitLoong\MigrationsGenerator\Database\Models\SQLSrv;

use KitLoong\MigrationsGenerator\Database\Models\DatabaseTable;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Index;
use KitLoong\MigrationsGenerator\Schema\Models\UDTColumn;

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
    protected function makeUDTColumn(string $table, array $column): UDTColumn
    {
        return new SQLSrvUDTColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeIndex(string $table, array $index, bool $hasUDTColumn): Index
    {
        return new SQLSrvIndex($table, $index, $hasUDTColumn);
    }
}
