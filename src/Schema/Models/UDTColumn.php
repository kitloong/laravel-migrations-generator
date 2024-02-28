<?php

namespace KitLoong\MigrationsGenerator\Schema\Models;

/**
 * Table column. User-defined type column which is not supported by the framework.
 */
interface UDTColumn extends Model
{
    /**
     * Get the column name.
     */
    public function getName(): string;

    /**
     * Get the table name.
     */
    public function getTableName(): string;

    /**
     * Get the ALTER table ADD column SQL.
     *
     * @return string[]
     */
    public function getSqls(): array;
}
