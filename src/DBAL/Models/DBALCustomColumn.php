<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models;

use Doctrine\DBAL\Schema\Column as DoctrineDBALColumn;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use KitLoong\MigrationsGenerator\DBAL\Connection;
use KitLoong\MigrationsGenerator\Schema\Models\CustomColumn;
use KitLoong\MigrationsGenerator\Support\CheckLaravelVersion;

abstract class DBALCustomColumn implements CustomColumn
{
    use CheckLaravelVersion;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string[]
     */
    private $sqls;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __construct(string $table, DoctrineDBALColumn $column)
    {
        $this->name      = $column->getName();
        $this->tableName = $table;

        // COLLATE clause cannot be used on user-defined data types.
        // Unset collation here.
        $platformOptions = $column->getPlatformOptions();
        unset($platformOptions['collation']);
        $column->setPlatformOptions($platformOptions);

        $tableDiff = $this->getTableDiff($column);

        $this->sqls = app(Connection::class)->getDoctrineConnection()->getDatabasePlatform()->getAlterTableSQL($tableDiff);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @inheritDoc
     */
    public function getSqls(): array
    {
        return $this->sqls;
    }

    /**
     * Initialize a TableDiff instance based on the table name.
     * The \Doctrine\DBAL\Schema\TableDiff constructor has been updated from Doctrine DBAL 3 to DBAL 4.
     * This method utilizes the Laravel version to determine which TableDiff constructor to use, as Laravel 11 requires Doctrine DBAL 4.
     *
     * @see  https://github.com/doctrine/dbal/pull/5683
     */
    private function getTableDiff(DoctrineDBALColumn $column): TableDiff
    {
        if ($this->atLeastLaravel11()) {
            return new TableDiff(
                new Table($this->tableName),
                [$column],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                []
            );
        }

        // @phpstan-ignore-next-line
        return new TableDiff($this->tableName, [$column]);
    }
}
