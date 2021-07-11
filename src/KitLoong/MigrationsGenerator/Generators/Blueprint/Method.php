<?php

namespace KitLoong\MigrationsGenerator\Generators\Blueprint;

class Method
{
    /** @var string */
    private $name;

    /** @var array */
    private $values;

    /** @var Method[] */
    private $chains;

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
        $this->chains = [];
    }

    /**
     * @param  string  $name
     * @param  ...$values
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\Method
     */
    public function chain(string $name, ...$values): Method
    {
        $this->chains[] = new Method($name, ...$values);
        return $this;
    }

    public function countChain(): int
    {
        return count($this->chains);
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

    /**
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\Method[]
     */
    public function getChains(): array
    {
        return $this->chains;
    }
}
