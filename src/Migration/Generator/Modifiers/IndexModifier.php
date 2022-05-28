<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Modifiers;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Support\IndexNameHelper;

class IndexModifier implements Modifier
{
    private $indexNameHelper;

    public function __construct(IndexNameHelper $indexNameHelper)
    {
        $this->indexNameHelper = $indexNameHelper;
    }

    /**
     * @inheritDoc
     */
    public function chain(Method $method, Table $table, Column $column, ...$args): Method
    {
        $chainableIndexes = $args[0];

        /** @var \Illuminate\Support\Collection<string, \KitLoong\MigrationsGenerator\Schema\Models\Index> $chainableIndexes Key is column name. */
        if (!$chainableIndexes->has($column->getName())) {
            return $method;
        }

        /** @var \KitLoong\MigrationsGenerator\Schema\Models\Index $index */
        $index = $chainableIndexes->get($column->getName());

        // "increment" will add primary key by default. No need explicitly declare "primary" index here.
        if ($column->isAutoincrement() && $index->getType()->equals(IndexType::PRIMARY())) {
            return $method;
        }

        if ($this->indexNameHelper->shouldSkipName($table->getName(), $index)) {
            $method->chain($index->getType());
            return $method;
        }

        // Chainable "fulltext" is lowercase.
        if ($index->getType()->equals(IndexType::FULLTEXT())) {
            $method->chain(IndexType::FULLTEXT_CHAIN(), $index->getName());
            return $method;
        }

        $method->chain($index->getType(), $index->getName());
        return $method;
    }
}
