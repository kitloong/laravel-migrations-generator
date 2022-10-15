<?php

namespace KitLoong\MigrationsGenerator\Schema\Models;

interface View extends Model
{
    /**
     * Get the view name, always unquoted.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the view create definition.
     *
     * @return string
     */
    public function getDefinition(): string;

    /**
     * Get the view drop definition.
     *
     * @return string
     */
    public function getDropDefinition(): string;
}
