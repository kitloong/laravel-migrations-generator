<?php

namespace KitLoong\MigrationsGenerator\Repositories\Entities\SQLSrv;

class ViewDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $definition;

    public function __construct(string $name, string $definition)
    {
        $this->name       = $name;
        $this->definition = $definition;
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
