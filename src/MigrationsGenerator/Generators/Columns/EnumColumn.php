<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Repositories\MySQLRepository;

class EnumColumn implements GeneratableColumn
{
    private $mysqlRepository;

    public function __construct(MySQLRepository $mySQLRepository)
    {
        $this->mysqlRepository = $mySQLRepository;
    }

    public function generate(string $type, Table $table, Column $column): Method
    {
        $values = $this->mysqlRepository->getEnumPresetValues($table->getName(), $column->getName());
        return new Method($type, $column->getName(), $values);
    }
}
