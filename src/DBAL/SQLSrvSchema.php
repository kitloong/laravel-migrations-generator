<?php

namespace KitLoong\MigrationsGenerator\DBAL;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\DBAL\Models\SQLSrv\SQLSrvForeignKey;
use KitLoong\MigrationsGenerator\DBAL\Models\SQLSrv\SQLSrvTable;
use KitLoong\MigrationsGenerator\DBAL\Models\SQLSrv\SQLSrvView;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Schema\Models\View;

class SQLSrvSchema extends DBALSchema
{
    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTable(string $name): Table
    {
        return new SQLSrvTable(
            $this->DBALSchema->listTableDetails($name),
            $this->DBALSchema->listTableColumns($name),
            $this->DBALSchema->listTableIndexes($name)
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
        return (new Collection($this->DBALSchema->listViews()))
            ->map(function (DoctrineDBALView $view) {
                return new SQLSrvView($view);
            });
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTableForeignKeys(string $table): Collection
    {
        return (new Collection($this->DBALSchema->listTableForeignKeys($table)))
            ->map(function (ForeignKeyConstraint $foreignKeyConstraint) use ($table) {
                return new SQLSrvForeignKey($table, $foreignKeyConstraint);
            });
    }
}
