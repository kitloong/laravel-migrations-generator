<?php

namespace KitLoong\MigrationsGenerator\Repositories\Entities;

class ProcedureDefinition
{
    public function __construct(private readonly string $name, private readonly string $definition)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDefinition(): string
    {
        return $this->definition;
    }
}
