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
     * Get the index column lengths, always same size with {@see self::getColumns()}.
     *
     * @return array<int|null>
     */
    public function getLengths(): array;

    /**
     * Get the index type.
     */
    public function getType(): IndexType;
}
