<?php

namespace KitLoong\MigrationsGenerator\Generators\Blueprint;

class ColumnMethod extends Method
{
    /** @var Method[] */
    private $chains;

    /**
     * Column name list to be merged with this column.
     * A common use case is `created_at` and `updated_at` can be merged into `timestamps`.
     *
     * @var string[]
     */
    private $mergeColumns;

    public function __construct(string $name, ...$values)
    {
        parent::__construct($name, ...$values);

        $this->chains       = [];
        $this->mergeColumns = [];
    }

    /**
     * Chain method.
     *
     * @param  string  $name
     * @param  ...$values
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod
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
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod[]
     */
    public function getChains(): array
    {
        return $this->chains;
    }

    /**
     * @return bool
     */
    public function hasMergeColumns(): bool
    {
        return count($this->mergeColumns) > 0;
    }

    /**
     * @return string[]
     */
    public function getMergeColumns(): array
    {
        return $this->mergeColumns;
    }

    /**
     * @param  string[]  $mergeColumns
     */
    public function setMergeColumns(array $mergeColumns): void
    {
        $this->mergeColumns = $mergeColumns;
    }
}
