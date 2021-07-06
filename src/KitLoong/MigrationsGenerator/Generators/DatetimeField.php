<?php

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzImmutableType;
use Doctrine\DBAL\Types\DateTimeTzType;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnName;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository;
use KitLoong\MigrationsGenerator\Support\Regex;
use KitLoong\MigrationsGenerator\Types\DBALTypes;

class DatetimeField
{
    const SQLSRV_DATETIME_DEFAULT_SCALE = 3;
    const SQLSRV_DATETIME_DEFAULT_LENGTH = 8;

    const SQLSRV_DATETIME_TZ_DEFAULT_SCALE = 7;
    const SQLSRV_DATETIME_TZ_DEFAULT_LENGTH = 10;

    private $decorator;
    private $mySQLRepository;
    private $pgSQLRepository;
    private $sqlSrvRepository;
    private $regex;

    public function __construct(
        Decorator $decorator,
        MySQLRepository $mySQLRepository,
        PgSQLRepository $pgSQLRepository,
        SQLSrvRepository $sqlSrvRepository,
        Regex $regex
    ) {
        $this->decorator = $decorator;
        $this->mySQLRepository = $mySQLRepository;
        $this->pgSQLRepository = $pgSQLRepository;
        $this->sqlSrvRepository = $sqlSrvRepository;
        $this->regex = $regex;
    }

    public function makeField(string $table, array $field, Column $column, bool $useTimestamps): array
    {
        if ($useTimestamps) {
            if ($field['field'] === ColumnName::CREATED_AT) {
                return [];
            } elseif ($field['field'] === ColumnName::UPDATED_AT) {
                $field['type'] = ColumnType::TIMESTAMPS;
                $field['field'] = null;
            }
        }

        if ($field['field'] === ColumnName::DELETED_AT && !$column->getNotnull()) {
            $field['type'] = ColumnType::SOFT_DELETES;
            $field['field'] = null;
        }

        if (isset(FieldGenerator::$fieldTypeMap[$field['type']])) {
            $field['type'] = FieldGenerator::$fieldTypeMap[$field['type']];
        }

        $length = $this->getLength($table, $column);
        if ($length !== null && $length > 0) {
            if ($field['type'] === ColumnType::SOFT_DELETES) {
                $field['field'] = ColumnName::DELETED_AT;
            }
            $field['args'][] = $length;
        }

        if (app(MigrationsGeneratorSetting::class)->getPlatform() === Platform::MYSQL) {
            if ($column->getType()->getName() === DBALTypes::TIMESTAMP) {
                if ($this->mySQLRepository->useOnUpdateCurrentTimestamp($table, $column->getName())) {
                    $field['decorators'][] = ColumnModifier::USE_CURRENT_ON_UPDATE;
                }
            }
        }

        return $field;
    }

    public function makeDefault(Column $column): string
    {
        if (in_array($column->getDefault(), ['CURRENT_TIMESTAMP'], true)) {
            return ColumnModifier::USE_CURRENT;
        } elseif ($column->getDefault() === 'now()') { // For PgSQL
            return ColumnModifier::USE_CURRENT;
        } else {
            $default = $this->decorator->columnDefaultToString($column->getDefault());
            return $this->decorator->decorate(ColumnModifier::DEFAULT, [$default]);
        }
    }

    /**
     * @param  Column[]  $columns
     * @return bool
     */
    public function isUseTimestamps(array $columns): bool
    {
        /** @var Column[] $timestampsColumns */
        $timestampsColumns = [];
        foreach ($columns as $column) {
            if ($column->getName() === ColumnName::CREATED_AT || $column->getName() === ColumnName::UPDATED_AT) {
                $timestampsColumns[] = $column;
            }
        }

        $useTimestamps = false;

        if (count($timestampsColumns) === 2) {
            $useTimestamps = true;
            foreach ($timestampsColumns as $timestamp) {
                if ($timestamp->getNotnull() || $timestamp->getDefault() !== null) {
                    $useTimestamps = false;
                }
            }
        }
        return $useTimestamps;
    }

    private function getLength(string $table, Column $column): ?int
    {
        switch (app(MigrationsGeneratorSetting::class)->getPlatform()) {
            case Platform::POSTGRESQL:
                return $this->getPgSQLLength($table, $column);
            case Platform::SQLSERVER:
                return $this->getSQLSrvLength($table, $column);
            default:
                return $column->getLength();
        }
    }

    /**
     * @param  string  $table
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return int|null
     */
    private function getPgSQLLength(string $table, Column $column): ?int
    {
        $rawType = ($this->pgSQLRepository->getTypeByColumnName($table, $column->getName()));
        $length = $this->regex->getTextBetween($rawType);
        if ($length !== null) {
            return (int) $length;
        } else {
            return null;
        }
    }

    /**
     * @param  string  $table
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return int|null
     */
    private function getSQLSrvLength(string $table, Column $column): ?int
    {
        $colDef = $this->sqlSrvRepository->getColumnDefinition($table, $column->getName());

        switch (get_class($column->getType())) {
            case DateTimeType::class:
            case DateTimeImmutableType::class:
                if ($colDef->getScale() === self::SQLSRV_DATETIME_DEFAULT_SCALE &&
                    $colDef->getLength() === self::SQLSRV_DATETIME_DEFAULT_LENGTH) {
                    return null;
                } else {
                    return $column->getScale();
                }
            case DateTimeTzType::class:
            case DateTimeTzImmutableType::class:
                if ($colDef->getScale() === self::SQLSRV_DATETIME_TZ_DEFAULT_SCALE &&
                    $colDef->getLength() === self::SQLSRV_DATETIME_TZ_DEFAULT_LENGTH) {
                    return null;
                } else {
                    return $column->getScale();
                }
            default:
                return $column->getScale();
        }
    }
}
