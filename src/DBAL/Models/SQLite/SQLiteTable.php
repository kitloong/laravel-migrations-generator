<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\SQLite;

use Doctrine\DBAL\Schema\Column as DoctrineDBALColumn;
use Doctrine\DBAL\Schema\Index as DoctrineDBALIndex;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALTable;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Index;

class SQLiteTable extends DBALTable
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
        return new SQLiteColumn($table, $column);
    }

    /**
     * @inheritDoc
     */
    protected function makeIndex(string $table, DoctrineDBALIndex $index): Index
    {
        return new SQLiteIndex($table, $index);
    }
}
