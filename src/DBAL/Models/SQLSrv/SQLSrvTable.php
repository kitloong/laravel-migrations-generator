<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\SQLSrv;

use Doctrine\DBAL\Schema\Column as DoctrineDBALColumn;
use Doctrine\DBAL\Schema\Index as DoctrineDBALIndex;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALTable;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Index;

class SQLSrvTable extends DBALTable
{
    /**
     * @inheritDoc
     */
    protected function handle(): void
    {
    }

    /**
     * @inheritDoc
     */
    protected function makeColumn(string $table, DoctrineDBALColumn $column): Column
    {
        return new SQLSrvColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeIndex(string $table, DoctrineDBALIndex $index): Index
    {
        return new SQLSrvIndex($table, $index);
    }
}
