<?php

namespace KitLoong\MigrationsGenerator\DBAL;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\DBAL\Models\PgSQL\PgSQLForeignKey;
use KitLoong\MigrationsGenerator\DBAL\Models\PgSQL\PgSQLTable;
use KitLoong\MigrationsGenerator\DBAL\Models\PgSQL\PgSQLView;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Schema\Models\View;

class PgSQLSchema extends DBALSchema
{
    /**
     * @inheritDoc
     */
    public function getTableNames(): Collection
    {
        return parent::getTableNames()
            ->filter(function (string $table): bool {
                // Checks if the table is from user defined "schema".
                // If table name do not have namespace, it is using the default namespace.
                if (strpos($table, '.') === false) {
                    return true;
                }

                // Schema name defined in the framework configuration.
                $schema = DB::connection()->getConfig('schema');

                $parts     = explode('.', $table);
                $namespace = $parts[0];

                return $namespace === $schema;
            })
            ->values();
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTable(string $name): Table
    {
        return new PgSQLTable(
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
        return $this->getViews()
            ->map(function (View $view) {
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
            ->filter(function (DoctrineDBALView $view) {
                if (in_array($view->getName(), ['public.geography_columns', 'public.geometry_columns'])) {
                    return false;
                }

                return $view->getNamespaceName() === DB::connection()->getConfig('schema');
            })
            ->map(function (DoctrineDBALView $view) {
                return new PgSQLView($view);
            })
            ->values();
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTableForeignKeys(string $table): Collection
    {
        return (new Collection($this->dbalSchema->listTableForeignKeys($table)))
            ->map(function (ForeignKeyConstraint $foreignKeyConstraint) use ($table) {
                return new PgSQLForeignKey($table, $foreignKeyConstraint);
            });
    }
}
