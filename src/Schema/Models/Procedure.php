<?php

namespace KitLoong\MigrationsGenerator\Schema\Models;

interface Procedure extends Model
{
    /**
     * Get the stored procedure name.
     */
    public function getName(): string;

    /**
     * Get the stored procedure create definition.
     */
    public function getDefinition(): string;

    /**
     * Get the stored procedure drop definition.
     */
    public function getDropDefinition(): string;
}
