<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\Schema\Builder;
use MigrationsGenerator\DBAL\Platform;
use MigrationsGenerator\Generators\Blueprint\ColumnMethod;
use MigrationsGenerator\Generators\MigrationConstants\ColumnName;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;
use MigrationsGenerator\MigrationsGeneratorSetting;
use MigrationsGenerator\Repositories\PgSQLRepository;
use MigrationsGenerator\Repositories\SQLSrvRepository;
use MigrationsGenerator\Support\Regex;

class StringColumn implements GeneratableColumn
{
    const SQLSRV_TEXT_TYPE   = 'nvarchar';
    const SQLSRV_TEXT_LENGTH = -1;

    private $pgSQLRepository;
    private $sqlSrvRepository;
    private $regex;

    public function __construct(
        PgSQLRepository $pgSQLRepository,
        SQLSrvRepository $sqlSrvRepository,
        Regex $regex
    ) {
        $this->pgSQLRepository  = $pgSQLRepository;
        $this->sqlSrvRepository = $sqlSrvRepository;
        $this->regex            = $regex;
    }

    public function generate(string $type, Table $table, Column $column): ColumnMethod
    {
        switch (app(MigrationsGeneratorSetting::class)->getPlatform()) {
            // It could be pgsql enum
            case Platform::POSTGRESQL:
                $values = $this->getPgSQLEnumValues($table->getName(), $column->getName());
                if (!empty($values)) {
                    return new ColumnMethod(ColumnType::ENUM, $column->getName(), $values);
                }
                break;
            // It could be sqlsrv text
            case Platform::SQLSERVER:
                $colDef = $this->sqlSrvRepository->getColumnDefinition($table->getName(), $column->getName());
                if ($colDef->getType() === self::SQLSRV_TEXT_TYPE &&
                    $colDef->getLength() === self::SQLSRV_TEXT_LENGTH) {
                    return new ColumnMethod(ColumnType::TEXT, $column->getName());
                }
                break;
            default:
        }

        if ($column->getName() === ColumnName::REMEMBER_TOKEN &&
            $column->getLength() === 100 &&
            !$column->getFixed()) {
            return new ColumnMethod(ColumnType::REMEMBER_TOKEN);
        }

        if ($column->getFixed()) {
            $columnType = ColumnType::CHAR;
        } else {
            $columnType = $type;
        }

        if ($column->getLength() !== null && $column->getLength() !== Builder::$defaultStringLength) {
            return new ColumnMethod($columnType, $column->getName(), $column->getLength());
        } else {
            return new ColumnMethod($columnType, $column->getName());
        }
    }

    private function getPgSQLEnumValues(string $tableName, string $column): array
    {
        $definition = $this->pgSQLRepository->getCheckConstraintDefinition($tableName, $column);
        if (!empty($definition)) {
            $enumValues = $this->regex->getTextBetweenAll($definition, "'", "'::");
            if ($enumValues !== null) {
                return $enumValues;
            }
        }
        return [];
    }
}
