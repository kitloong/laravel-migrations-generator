<?php

namespace KitLoong\MigrationsGenerator\Schema\Models;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;

interface Index extends Model
{
    /**
     * Get the index name. An index name could be empty.
     * Empty name means the index uses the default name defined by the database platform.
     */
    public function getName(): string;

    /**
     * Get the table name.
     */
    public function getTableName(): string;

    /**
     * Get the index column names.
     *
     * @return string[]
     */
    public function getColumns(): array;

    /**
     * Get the index type.
     */
    public function getType(): IndexType;

    /**
     * Get the raw add index SQL queries.
     * This method is used when the index has a user-defined type column.
     * If the index does not have a user-defined type column, this method will return an empty array.
     *
     * @return string[]
     */
    public function getUDTColumnSqls(): array;

    /**
     * Indicates if this index column(s) is a user-defined type.
     */
    public function hasUDTColumn(): bool;
}
