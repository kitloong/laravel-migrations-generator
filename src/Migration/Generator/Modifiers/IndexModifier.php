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
        /** @var \Illuminate\Support\Collection<string, \KitLoong\MigrationsGenerator\Schema\Models\Index> $chainableIndexes Key is column name. */
        $chainableIndexes = $args[0];

        if (!$chainableIndexes->has($column->getName())) {
            return $method;
        }

        /** @var \KitLoong\MigrationsGenerator\Schema\Models\Index $index */
        $index = $chainableIndexes->get($column->getName());

        // "increment" will add primary key by default. No need explicitly declare "primary" index here.
        if ($column->isAutoincrement() && $index->getType()->equals(IndexType::PRIMARY())) {
            return $method;
        }

        $indexType = $this->adjustIndexType($index->getType());

        if ($this->indexNameHelper->shouldSkipName($table->getName(), $index)) {
            $method->chain($indexType);
            return $method;
        }

        $method->chain($indexType, $index->getName());
        return $method;
    }

    /**
     * FULLTEXT index method name is `fullText` (camelCase) but changed to `fulltext` (lowercase)
     * when used for column chaining.
     *
     * @param  \KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType  $indexType
     * @return \KitLoong\MigrationsGenerator\Enum\Migrations\Method\IndexType
     */
    private function adjustIndexType(IndexType $indexType): IndexType
    {
        if ($indexType->equals(IndexType::FULLTEXT())) {
            return IndexType::FULLTEXT_CHAIN();
        }

        return $indexType;
    }
}
