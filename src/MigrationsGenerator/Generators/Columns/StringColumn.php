<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\Schema\Builder;
use MigrationsGenerator\DBAL\Platform;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\ColumnName;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;
use MigrationsGenerator\MigrationsGeneratorSetting;
use MigrationsGenerator\Repositories\PgSQLRepository;
use MigrationsGenerator\Repositories\SQLSrvRepository;
use MigrationsGenerator\Support\Regex;

class StringColumn implements GeneratableColumn
{
    public const SQLSRV_TEXT_TYPE      = 'nvarchar';
    public const SQLSRV_TEXT_LENGTH    = -1;
    public const REMEMBER_TOKEN_LENGTH = 100;

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

    public function generate(string $type, Table $table, Column $column): Method
    {
        switch (app(MigrationsGeneratorSetting::class)->getPlatform()) {
            // It could be pgsql enum
            case Platform::POSTGRESQL:
                $values = $this->getPgSQLEnumValues($table->getName(), $column->getName());
                if (!empty($values)) {
                    return new Method(ColumnType::ENUM, $column->getName(), $values);
                }
                break;
            // It could be sqlsrv text
            case Platform::SQLSERVER:
                $colDef = $this->sqlSrvRepository->getColumnDefinition($table->getName(), $column->getName());
                if ($colDef->getType() === self::SQLSRV_TEXT_TYPE &&
                    $colDef->getLength() === self::SQLSRV_TEXT_LENGTH) {
                    return new Method(ColumnType::TEXT, $column->getName());
                }
                break;
            default:
        }

        if ($column->getName() === ColumnName::REMEMBER_TOKEN &&
            $column->getLength() === self::REMEMBER_TOKEN_LENGTH &&
            !$column->getFixed()) {
            return new Method(ColumnType::REMEMBER_TOKEN);
        }

        if ($column->getFixed()) {
            $columnType = ColumnType::CHAR;
        } else {
            $columnType = $type;
        }

        if ($column->getLength() !== null && $column->getLength() !== Builder::$defaultStringLength) {
            return new Method($columnType, $column->getName(), $column->getLength());
        } else {
            return new Method($columnType, $column->getName());
        }
    }

    /**
     * Get PgSQL enum values.
     *
     * @param  string  $table  Table name.
     * @param  string  $column  Column name.
     * @return string[]
     */
    private function getPgSQLEnumValues(string $table, string $column): array
    {
        $definition = $this->pgSQLRepository->getCheckConstraintDefinition($table, $column);
        if (!empty($definition)) {
            $enumValues = $this->regex->getTextBetweenAll($definition, "'", "'::");
            if ($enumValues !== null) {
                return $enumValues;
            }
        }
        return [];
    }
}
