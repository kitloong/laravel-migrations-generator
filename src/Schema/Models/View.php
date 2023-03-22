<?php

namespace KitLoong\MigrationsGenerator\Schema\Models;

interface View extends Model
{
    /**
     * Get the view name, always unquoted.
     */
    public function getName(): string;

    /**
     * Get the view create definition.
     */
    public function getDefinition(): string;

    /**
     * Get the view drop definition.
     */
    public function getDropDefinition(): string;
}
