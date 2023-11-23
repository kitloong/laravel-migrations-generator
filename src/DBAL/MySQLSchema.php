<?php

namespace KitLoong\MigrationsGenerator\DBAL;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\DBAL\Models\MySQL\MySQLForeignKey;
use KitLoong\MigrationsGenerator\DBAL\Models\MySQL\MySQLProcedure;
use KitLoong\MigrationsGenerator\DBAL\Models\MySQL\MySQLTable;
use KitLoong\MigrationsGenerator\DBAL\Models\MySQL\MySQLView;
use KitLoong\MigrationsGenerator\Repositories\Entities\ProcedureDefinition;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Schema\Models\View;
use KitLoong\MigrationsGenerator\Schema\MySQLSchema as MySQLSchemaInterface;

/**
 * @extends \KitLoong\MigrationsGenerator\DBAL\DBALSchema<\Doctrine\DBAL\Platforms\MySQLPlatform>
 */
class MySQLSchema extends DBALSchema implements MySQLSchemaInterface
{
    /**
     * @var \KitLoong\MigrationsGenerator\Repositories\MySQLRepository
     */
    private $mySQLRepository;

    public function __construct(RegisterColumnType $registerColumnType, MySQLRepository $mySQLRepository)
    {
        parent::__construct($registerColumnType);

        $this->mySQLRepository = $mySQLRepository;
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTable(string $name): Table
    {
        return new MySQLTable(
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
                return new MySQLView($view);
            });
    }

    /**
     * @inheritDoc
     */
    public function getProcedures(): Collection
    {
        return (new Collection($this->mySQLRepository->getProcedures()))
            ->map(function (ProcedureDefinition $procedureDefinition) {
                return new MySQLProcedure($procedureDefinition->getName(), $procedureDefinition->getDefinition());
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
                return new MySQLForeignKey($table, $foreignKeyConstraint);
            });
    }
}
