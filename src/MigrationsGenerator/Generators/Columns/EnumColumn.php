<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\ColumnMethod;
use MigrationsGenerator\Repositories\MySQLRepository;

class EnumColumn implements GeneratableColumn
{
    private $mysqlRepository;

    public function __construct(MySQLRepository $mySQLRepository)
    {
        $this->mysqlRepository = $mySQLRepository;
    }

    public function generate(string $type, Table $table, Column $column): ColumnMethod
    {
        $values = $this->mysqlRepository->getEnumPresetValues($table->getName(), $column->getName());
        return new ColumnMethod($type, $column->getName(), $values);
    }
}
