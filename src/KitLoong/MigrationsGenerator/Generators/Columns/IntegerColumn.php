<?php

namespace KitLoong\MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;

class IntegerColumn implements GeneratableColumn
{
    private $mySQLRepository;
    private $setting;

    public function __construct(MySQLRepository $mySQLRepository, MigrationsGeneratorSetting $setting)
    {
        $this->mySQLRepository = $mySQLRepository;
        $this->setting         = $setting;
    }

    public function generate(string $type, Table $table, Column $column): ColumnMethod
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

    private function generateAsBoolean(Column $column): ColumnMethod
    {
        $method =  new ColumnMethod(ColumnType::BOOLEAN, $column->getName());
        if ($column->getUnsigned()) {
            $method->chain(ColumnModifier::UNSIGNED);
        }
        return $method;
    }

    private function generateAsInteger(string $type, Column $column): ColumnMethod
    {
        if ($column->getUnsigned() && $column->getAutoincrement()) {
            if ($type === ColumnType::INTEGER) {
                return new ColumnMethod(ColumnType::INCREMENTS, $column->getName());
            } else {
                // bigIncrements, smallIncrements, etc
                return new ColumnMethod(str_replace('Integer', 'Increments', $type), $column->getName());
            }
//            $indexes->forget($field['field']);
        } else {
            $methodType = $type;
            if ($column->getUnsigned()) {
                // unsignedBigInteger, unsignedSmallInteger, etc
                $methodType = 'unsigned'.ucfirst($type);
            }

            if ($column->getAutoincrement()) {
                return new ColumnMethod($methodType, $column->getName(), true);
//                $indexes->forget($field['field']);
            }
            return new ColumnMethod($methodType, $column->getName());
        }
    }

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
