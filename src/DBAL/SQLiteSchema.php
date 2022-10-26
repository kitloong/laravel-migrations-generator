<?php

namespace KitLoong\MigrationsGenerator\DBAL;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\DBAL\Models\SQLite\SQLiteForeignKey;
use KitLoong\MigrationsGenerator\DBAL\Models\SQLite\SQLiteTable;
use KitLoong\MigrationsGenerator\DBAL\Models\SQLite\SQLiteView;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Schema\Models\View;

class SQLiteSchema extends DBALSchema
{
    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTable(string $name): Table
    {
        return new SQLiteTable(
            $this->introspectTable($name),
            $this->dbalSchema->listTableColumns($name),
            $this->dbalSchema->listTableIndexes($name)
        );
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getViewNames(): Collection
    {
        return $this->getViews()->map(function (View $view) {
            return $view->getName();
        });
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getViews(): Collection
    {
        return (new Collection($this->dbalSchema->listViews()))
            ->map(function (DoctrineDBALView $view) {
                return new SQLiteView($view);
            });
    }

    /**
     * @inheritDoc
     */
    public function getProcedures(): Collection
    {
        // Stored procedure is not available.
        // https://sqlite.org/forum/info/78a60bdeec7c1ee9
        return new Collection();
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTableForeignKeys(string $table): Collection
    {
        return (new Collection($this->dbalSchema->listTableForeignKeys($table)))
            ->map(function (ForeignKeyConstraint $foreignKeyConstraint) use ($table) {
                return new SQLiteForeignKey($table, $foreignKeyConstraint);
            });
    }
}
