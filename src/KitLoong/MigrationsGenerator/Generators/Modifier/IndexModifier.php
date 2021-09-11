<?php

namespace KitLoong\MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod;
use KitLoong\MigrationsGenerator\Generators\IndexGenerator;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class IndexModifier
{
    private $indexGenerator;

    public function __construct(IndexGenerator $indexGenerator)
    {
        $this->indexGenerator = $indexGenerator;
    }

    /**
     * @param  string  $table
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
     * @param  \Illuminate\Support\Collection<string, \Doctrine\DBAL\Schema\Index>  $singleColumnIndexes
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod
     */
    public function chainIndex(string $table, ColumnMethod $method, Collection $singleColumnIndexes, Column $column): ColumnMethod
    {
        if ($singleColumnIndexes->has($column->getName())) {
            /** @var Index $index */
            $index = $singleColumnIndexes->get($column->getName());

            // autoIncrement is handled in IntegerColumn
            if ($index->isPrimary() && $column->getAutoincrement()) {
                return $method;
            }

            $indexType = $this->indexGenerator->getIndexType($index);
            if ($index->isPrimary() ||
                app(MigrationsGeneratorSetting::class)->isIgnoreIndexNames() ||
                $this->shouldSkipName($table, $index, $indexType)) {
                $method->chain($indexType);
            } else {
                $method->chain($indexType, $index->getName());
            }
        }
        return $method;
    }

    /**
     * @param  string  $table
     * @param  Index  $index
     * @param  string  $type
     * @return bool
     */
    private function shouldSkipName(string $table, Index $index, string $type): bool
    {
        $guessIndexName = strtolower($table.'_'.implode('_', $index->getColumns()).'_'.$type);
        $guessIndexName = str_replace(['-', '.'], '_', $guessIndexName);
        return $guessIndexName === $index->getName();
    }
}
