<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

class Method
{
    /** @var string */
    private $name;

    /** @var mixed */
    private $values;

    /** @var \KitLoong\MigrationsGenerator\Migration\Blueprint\Method[] */
    private $chains;

    /**
     * Method constructor.
     *
     * @param  string  $name  Method name.
     * @param  mixed  ...$values  Method arguments.
     */
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
     * @param  string  $name  Method name.
     * @param  mixed  ...$values  Method arguments.
     * @return $this
     */
    public function chain(string $name, ...$values): Method
    {
        $this->chains[] = new Method($name, ...$values);
        return $this;
    }

    /**
     * Checks if chain name exists.
     *
     * @param  string  $name  Method name.
     * @return bool
     */
    public function hasChain(string $name): bool
    {
        foreach ($this->chains as $chain) {
            if ($chain->getName() === $name) {
                return true;
            }
        }

        return false;
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
     * Get a list of chained methods.
     *
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Method[]
     */
    public function getChains(): array
    {
        return $this->chains;
    }
}
