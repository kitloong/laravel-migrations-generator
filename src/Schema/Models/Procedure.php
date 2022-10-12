<?php

namespace KitLoong\MigrationsGenerator\Schema\Models;

interface Procedure extends Model
{
    /**
     * Get the stored procedure name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the stored procedure create definition.
     *
     * @return string
     */
    public function getDefinition(): string;

    /**
     * Get the stored procedure drop definition.
     *
     * @return string
     */
    public function getDropDefinition(): string;
}
