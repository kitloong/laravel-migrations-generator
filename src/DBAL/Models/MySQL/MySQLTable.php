<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\MySQL;

use Doctrine\DBAL\Schema\Column as DoctrineDBALColumn;
use Doctrine\DBAL\Schema\Index as DoctrineDBALIndex;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALTable;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\CustomColumn;
use KitLoong\MigrationsGenerator\Schema\Models\Index;

class MySQLTable extends DBALTable
{
    /**
     * @inheritDoc
     */
    protected function handle(): void
    {
        // Do nothing.
    }

    /**
     * @inheritDoc
     */
    protected function makeColumn(string $table, DoctrineDBALColumn $column): Column
    {
        return new MySQLColumn($table, $column);
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    protected function makeCustomColumn(string $table, DoctrineDBALColumn $column): CustomColumn
    {
        return new MySQLCustomColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeIndex(string $table, DoctrineDBALIndex $index): Index
    {
        return new MySQLIndex($table, $index);
    }
}
