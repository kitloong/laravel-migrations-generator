<?php

namespace KitLoong\MigrationsGenerator\Schema\Models;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;

interface Index extends Model
{
    /**
     * Get the index name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the table name.
     *
     * @return string
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
     *
     * @return \KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType
     */
    public function getType(): IndexType;
}
