<?php

namespace MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Collection;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\IndexGenerator;

class IndexModifier
{
    private $indexGenerator;

    public function __construct(IndexGenerator $indexGenerator)
    {
        $this->indexGenerator = $indexGenerator;
    }

    /**
     * Set index.
     *
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @param  \Illuminate\Support\Collection<string, \Doctrine\DBAL\Schema\Index>  $singleColumnIndexes
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function chainIndex(Table $table, Method $method, Collection $singleColumnIndexes, Column $column): Method
    {
        if ($singleColumnIndexes->has($column->getName())) {
            /** @var Index $index */
            $index = $singleColumnIndexes->get($column->getName());

            // autoIncrement is handled in IntegerColumn
            if ($index->isPrimary() && $column->getAutoincrement()) {
                return $method;
            }

            $indexType = $this->indexGenerator->getIndexType($index);
            if ($this->indexGenerator->shouldSkipName($table->getName(), $index, $indexType)) {
                $method->chain($indexType);
            } else {
                $method->chain($indexType, $index->getName());
            }
        }
        return $method;
    }
}
