<?php

namespace KitLoong\MigrationsGenerator\Generators\Blueprint;

abstract class Method
{
    /** @var string */
    private $name;

    /** @var array */
    private $values;

    /**
     * Method constructor.
     *
     * @param  string  $name
     * @param  ...$values
     */
    public function __construct(string $name, ...$values)
    {
        $this->name   = $name;
        $this->values = $values;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
