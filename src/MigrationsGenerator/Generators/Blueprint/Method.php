<?php

namespace MigrationsGenerator\Generators\Blueprint;

class Method
{
    /** @var string */
    private $name;

    /** @var array */
    private $values;

    /** @var Method[] */
    private $chains;

    public function __construct(string $name, ...$values)
    {
        $this->name   = $name;
        $this->values = $values;
        $this->chains = [];
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
     * Chain method.
     *
     * @param  string  $name
     * @param  ...$values
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function chain(string $name, ...$values): Method
    {
        $this->chains[] = new Method($name, ...$values);
        return $this;
    }

    /**
     * Total chain.
     *
     * @return int
     */
    public function countChain(): int
    {
        return count($this->chains);
    }

    /**
     * @return \MigrationsGenerator\Generators\Blueprint\Method[]
     */
    public function getChains(): array
    {
        return $this->chains;
    }
}
