<?php

namespace MigrationsGenerator\Generators\Blueprint;

class ColumnMethod extends Method
{
    /** @var Method[] */
    private $chains;

    public function __construct(string $name, ...$values)
    {
        parent::__construct($name, ...$values);

        $this->chains = [];
    }

    /**
     * Chain method.
     *
     * @param  string  $name
     * @param  ...$values
     * @return \MigrationsGenerator\Generators\Blueprint\ColumnMethod
     */
    public function chain(string $name, ...$values): ColumnMethod
    {
        $this->chains[] = new ColumnMethod($name, ...$values);
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
     * @return \MigrationsGenerator\Generators\Blueprint\ColumnMethod[]
     */
    public function getChains(): array
    {
        return $this->chains;
    }
}
