<?php

namespace KitLoong\MigrationsGenerator\Repositories\Entities;

class ProcedureDefinition
{
    private $name;
    private $definition;

    public function __construct(string $name, string $definition)
    {
        $this->name       = $name;
        $this->definition = $definition;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDefinition(): string
    {
        return $this->definition;
    }
}
