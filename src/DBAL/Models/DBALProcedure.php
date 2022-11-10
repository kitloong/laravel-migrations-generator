<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models;

use KitLoong\MigrationsGenerator\Schema\Models\Procedure;

abstract class DBALProcedure implements Procedure
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $definition;

    /**
     * @var string
     */
    protected $dropDefinition;

    public function __construct(string $name, string $definition)
    {
        $this->name           = $name;
        $this->definition     = $definition;
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
