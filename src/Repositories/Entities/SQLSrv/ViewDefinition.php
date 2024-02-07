<?php

namespace KitLoong\MigrationsGenerator\Repositories\Entities\SQLSrv;

class ViewDefinition
{
    public function __construct(private string $name, private string $definition)
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
