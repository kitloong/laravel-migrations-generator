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

/**
 * @extends \KitLoong\MigrationsGenerator\DBAL\DBALSchema<\Doctrine\DBAL\Platforms\SQLServerPlatform>
 */
class SQLSrvSchema extends DBALSchema
{
    public function __construct(RegisterColumnType $registerColumnType, private SQLSrvRepository $sqlSrvRepository)
    {
        parent::__construct($registerColumnType);
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
            $this->dbalSchema->listTableIndexes($name),
        );
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getViewNames(): Collection
    {
        return $this->getViews()->map(static fn (View $view) => $view->getName());
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getViews(): Collection
    {
        return (new Collection($this->dbalSchema->listViews()))
            ->map(static fn (DoctrineDBALView $view) => new SQLSrvView($view))
            ->filter(static fn (SQLSrvView $view) => $view->getDefinition() !== '');
    }

    /**
     * @inheritDoc
     */
    public function getProcedures(): Collection
    {
        return (new Collection($this->sqlSrvRepository->getProcedures()))
            ->map(static fn (ProcedureDefinition $procedureDefinition) => new PgSQLProcedure($procedureDefinition->getName(), $procedureDefinition->getDefinition()));
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTableForeignKeys(string $table): Collection
    {
        // @phpstan-ignore-next-line
        return (new Collection($this->dbalSchema->listTableForeignKeys($table)))
            ->map(static fn (ForeignKeyConstraint $foreignKeyConstraint) => new SQLSrvForeignKey($table, $foreignKeyConstraint));
    }
}
