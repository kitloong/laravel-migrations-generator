<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnName;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;
use KitLoong\MigrationsGenerator\Types\DBALTypes;

class DatetimeField
{
    private $decorator;
    private $mySQLRepository;

    public function __construct(Decorator $decorator, MySQLRepository $mySQLRepository)
    {
        $this->decorator = $decorator;
        $this->mySQLRepository = $mySQLRepository;
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

        if ($column->getLength() !== null && $column->getLength() > 0) {
            if ($field['type'] === ColumnType::SOFT_DELETES) {
                $field['field'] = ColumnName::DELETED_AT;
            }
            $field['args'][] = $column->getLength();
        }

        if ($column->getType()->getName() === DBALTypes::TIMESTAMP) {
            if ($this->mySQLRepository->useOnUpdateCurrentTimestamp($table, $column->getName())) {
                $field['decorators'][] = ColumnModifier::USE_CURRENT_ON_UPDATE;
            }
        }
        return $field;
    }

    public function makeDefault(Column $column): string
    {
        if (in_array($column->getDefault(), ['CURRENT_TIMESTAMP'], true)) {
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
    public function isUseTimestamps($columns): bool
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
}
