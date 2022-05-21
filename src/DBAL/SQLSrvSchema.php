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
            $this->dbalSchema->listTableDetails($name),
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
                return new SQLSrvView($view);
            })
            ->filter(function (SQLSrvView $view) {
                // $view->getCreateViewSql() is empty string if the view definition is encrypted.
                return $view->getCreateViewSql() !== '';
            });
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTableForeignKeys(string $table): Collection
    {
        return (new Collection($this->dbalSchema->listTableForeignKeys($table)))
            ->map(function (ForeignKeyConstraint $foreignKeyConstraint) use ($table) {
                return new SQLSrvForeignKey($table, $foreignKeyConstraint);
            });
    }
}
