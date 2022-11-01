<?php

namespace KitLoong\MigrationsGenerator\DBAL;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\DBAL\Models\PgSQL\PgSQLProcedure;
use KitLoong\MigrationsGenerator\DBAL\Models\SQLSrv\SQLSrvForeignKey;
use KitLoong\MigrationsGenerator\DBAL\Models\SQLSrv\SQLSrvTable;
use KitLoong\MigrationsGenerator\DBAL\Models\SQLSrv\SQLSrvView;
use KitLoong\MigrationsGenerator\Repositories\Entities\ProcedureDefinition;
use KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Schema\Models\View;

class SQLSrvSchema extends DBALSchema
{
    private $sqlSrvRepository;

    public function __construct(RegisterColumnType $registerColumnType, SQLSrvRepository $sqlSrvRepository)
    {
        parent::__construct($registerColumnType);

        $this->sqlSrvRepository = $sqlSrvRepository;
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTable(string $name): Table
    {
        return new SQLSrvTable(
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
                return new SQLSrvView($view);
            })
            ->filter(function (SQLSrvView $view) {
                // `$view->getDefinition()` is empty string if the view definition is encrypted.
                return $view->getDefinition() !== '';
            });
    }

    /**
     * @inheritDoc
     */
    public function getProcedures(): Collection
    {
        $this->sqlSrvRepository->getProcedures();
        return (new Collection($this->sqlSrvRepository->getProcedures()))
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
                return new SQLSrvForeignKey($table, $foreignKeyConstraint);
            });
    }
}
