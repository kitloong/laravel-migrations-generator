<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator;

use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Migration\Generator\Modifiers\CharsetModifier;
use KitLoong\MigrationsGenerator\Migration\Generator\Modifiers\CollationModifier;
use KitLoong\MigrationsGenerator\Migration\Generator\Modifiers\CommentModifier;
use KitLoong\MigrationsGenerator\Migration\Generator\Modifiers\DefaultModifier;
use KitLoong\MigrationsGenerator\Migration\Generator\Modifiers\IndexModifier;
use KitLoong\MigrationsGenerator\Migration\Generator\Modifiers\NullableModifier;
use KitLoong\MigrationsGenerator\Migration\Generator\Modifiers\StoredAsModifier;
use KitLoong\MigrationsGenerator\Migration\Generator\Modifiers\VirtualAsModifier;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;

class ColumnGenerator
{
    public function __construct(
        private readonly CharsetModifier $charsetModifier,
        private readonly CollationModifier $collationModifier,
        private readonly CommentModifier $commentModifier,
        private readonly DefaultModifier $defaultModifier,
        private readonly IndexModifier $indexModifier,
        private readonly NullableModifier $nullableModifier,
        private readonly StoredAsModifier $storedAsModifier,
        private readonly VirtualAsModifier $virtualAsModifier,
    ) {
    }

    /**
     * @param  \Illuminate\Support\Collection<string, \KitLoong\MigrationsGenerator\Schema\Models\Index>  $chainableIndexes
     */
    public function generate(Table $table, Column $column, Collection $chainableIndexes): Method
    {
        $method = $this->createMethodFromColumn($table, $column);

        $method = $this->charsetModifier->chain($method, $table, $column);
        $method = $this->collationModifier->chain($method, $table, $column);
        $method = $this->nullableModifier->chain($method, $table, $column);
        $method = $this->defaultModifier->chain($method, $table, $column);
        $method = $this->virtualAsModifier->chain($method, $table, $column);
        $method = $this->storedAsModifier->chain($method, $table, $column);
        $method = $this->indexModifier->chain($method, $table, $column, $chainableIndexes);
        $method = $this->commentModifier->chain($method, $table, $column);

        return $method;
    }

    private function createMethodFromColumn(Table $table, Column $column): Method
    {
        /** @var \KitLoong\MigrationsGenerator\Migration\Generator\Columns\ColumnTypeGenerator $generator */
        $generator = app(ColumnType::class . '\\' . $column->getType()->name);
        return $generator->generate($table, $column);
    }
}
