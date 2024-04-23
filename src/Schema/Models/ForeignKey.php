<?php

namespace KitLoong\MigrationsGenerator\Schema\Models;

interface ForeignKey extends Model
{
    /**
     * Get the foreign key name.
     */
    public function getName(): ?string;

    /**
     * Get the table name.
     */
    public function getTableName(): string;

    /**
     * Get the foreign key local column names.
     *
     * @return string[]
     */
    public function getLocalColumns(): array;

    /**
     * Get the foreign key foreign column names.
     *
     * @return string[]
     */
    public function getForeignColumns(): array;

    /**
     * Get the foreign table name.
     */
    public function getForeignTableName(): string;

    /**
     * Get the foreign key "on update" action constraint.
     */
    public function getOnUpdate(): ?string;

    /**
     * Get the foreign key "on delete" action constraint.
     */
    public function getOnDelete(): ?string;
}
