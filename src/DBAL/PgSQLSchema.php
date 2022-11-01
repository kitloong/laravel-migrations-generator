<?php

namespace KitLoong\MigrationsGenerator\DBAL;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\DBAL\Models\PgSQL\PgSQLForeignKey;
use KitLoong\MigrationsGenerator\DBAL\Models\PgSQL\PgSQLProcedure;
use KitLoong\MigrationsGenerator\DBAL\Models\PgSQL\PgSQLTable;
use KitLoong\MigrationsGenerator\DBAL\Models\PgSQL\PgSQLView;
use KitLoong\MigrationsGenerator\Repositories\Entities\ProcedureDefinition;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Schema\Models\View;

class PgSQLSchema extends DBALSchema
{
    private $pgSQLRepository;

    public function __construct(RegisterColumnType $registerColumnType, PgSQLRepository $pgSQLRepository)
    {
        parent::__construct($registerColumnType);

        $this->pgSQLRepository = $pgSQLRepository;
    }

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
                $searchPath = DB::connection()->getConfig('search_path') ?: DB::connection()->getConfig('schema');

                $parts     = explode('.', $table);
                $namespace = $parts[0];

                return $namespace === $searchPath;
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

                // Start from Laravel 9, the `schema` configuration option used to configure Postgres connection search paths renamed to `search_path`.
                // Fallback to `schema` if Laravel version is older than 9.
                $searchPath = DB::connection()->getConfig('search_path') ?: DB::connection()->getConfig('schema');

                return $view->getNamespaceName() === $searchPath;
            })
            ->map(function (DoctrineDBALView $view) {
                return new PgSQLView($view);
            })
            ->values();
    }

    /**
     * @inheritDoc
     */
    public function getProcedures(): Collection
    {
        $this->pgSQLRepository->getProcedures();
        return (new Collection($this->pgSQLRepository->getProcedures()))
            ->map(function (ProcedureDefinition $procedureDefinition) {
                return new PgSQLProcedure($procedureDefinition->getName(), $procedureDefinition->getDefinition());
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
                return new PgSQLForeignKey($table, $foreignKeyConstraint);
            });
    }
}
