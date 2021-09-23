<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Str;
use MigrationsGenerator\DBAL\Platform;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;
use MigrationsGenerator\MigrationsGeneratorSetting;
use MigrationsGenerator\Repositories\MySQLRepository;

class IntegerColumn implements GeneratableColumn
{
    private $mySQLRepository;
    private $setting;

    public function __construct(MySQLRepository $mySQLRepository, MigrationsGeneratorSetting $setting)
    {
        $this->mySQLRepository = $mySQLRepository;
        $this->setting         = $setting;
    }

    public function generate(string $type, Table $table, Column $column): Method
    {
        // MySQL uses TINYINT(1) as boolean
        // Check if column type is TINYINT(1) and generate as `boolean` type
        if ($type === ColumnType::TINY_INTEGER &&
            !$column->getAutoincrement() &&
            $this->checkMySQLBoolean($table, $column)) {
            return $this->generateAsBoolean($column);
        }

        return $this->generateAsInteger($type, $column);
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    private function generateAsBoolean(Column $column): Method
    {
        $method = new Method(ColumnType::BOOLEAN, $column->getName());
        if ($column->getUnsigned()) {
            $method->chain(ColumnModifier::UNSIGNED);
        }
        return $method;
    }

    /**
     * @param  string  $type  Column type name.
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    private function generateAsInteger(string $type, Column $column): Method
    {
        if ($column->getUnsigned() && $column->getAutoincrement()) {
            if ($type === ColumnType::INTEGER) {
                return new Method(ColumnType::INCREMENTS, $column->getName());
            } else {
                // bigIncrements, smallIncrements, etc
                return new Method(str_replace('Integer', 'Increments', $type), $column->getName());
            }
        } else {
            $methodType = $type;
            if ($column->getUnsigned()) {
                // unsignedBigInteger, unsignedSmallInteger, etc
                $methodType = 'unsigned'.ucfirst($type);
            }

            if ($column->getAutoincrement()) {
                return new Method($methodType, $column->getName(), true);
            }
            return new Method($methodType, $column->getName());
        }
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return bool
     */
    private function checkMySQLBoolean(Table $table, Column $column): bool
    {
        if ($this->setting->getPlatform() !== Platform::MYSQL) {
            return false;
        }

        $showColumn = $this->mySQLRepository->showColumn($table->getName(), $column->getName());
        if ($showColumn !== null) {
            return Str::startsWith($showColumn->getType(), 'tinyint(1)');
        }

        return false;
    }
}
