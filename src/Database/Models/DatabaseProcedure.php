<?php

namespace KitLoong\MigrationsGenerator\Database\Models;

use KitLoong\MigrationsGenerator\Schema\Models\Procedure;

abstract class DatabaseProcedure implements Procedure
{
    protected string $dropDefinition;

    public function __construct(protected string $name, protected string $definition)
    {
        $this->dropDefinition = "DROP PROCEDURE IF EXISTS $name";
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getDefinition(): string
    {
        return $this->definition;
    }

    /**
     * @inheritDoc
     */
    public function getDropDefinition(): string
    {
        return $this->dropDefinition;
    }
}
