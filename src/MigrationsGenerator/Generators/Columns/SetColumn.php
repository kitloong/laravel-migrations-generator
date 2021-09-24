<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;
use MigrationsGenerator\Repositories\MySQLRepository;
use MigrationsGenerator\Support\CheckMigrationMethod;

class SetColumn implements GeneratableColumn
{
    use CheckMigrationMethod;

    private $mysqlRepository;

    public function __construct(MySQLRepository $mySQLRepository)
    {
        $this->mysqlRepository = $mySQLRepository;
    }

    public function generate(string $type, Table $table, Column $column): Method
    {
        if ($this->hasSet()) {
            $values = $this->mysqlRepository->getSetPresetValues($table->getName(), $column->getName());
            return new Method($type, $column->getName(), $values);
        } else {
            return new Method(ColumnType::STRING, $column->getName());
        }
    }
}
