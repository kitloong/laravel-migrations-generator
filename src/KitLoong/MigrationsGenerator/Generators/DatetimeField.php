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

class DatetimeField
{
    private $decorator;

    public function __construct(Decorator $decorator)
    {
        $this->decorator = $decorator;
    }

    public function makeField(array $field, Column $column, bool $useTimestamps): array
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
