<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 * Time: 14:52
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

        if ($column->getDefault() !== null) {
            if (in_array($column->getDefault(), ['CURRENT_TIMESTAMP'], true)) {
                $field['type'] = ColumnType::TIMESTAMP;
            }
        }
        if ($field['field'] === ColumnName::DELETED_AT && !$column->getNotnull()) {
            $field['type'] = ColumnType::SOFT_DELETES;
            $field['field'] = null;
        }

        if ($column->getLength() && $column->getLength() > 0) {
            $field['args'] = $column->getLength();
            if ($field['type'] === ColumnType::SOFT_DELETES) {
                $field['args'] = "'".ColumnName::DELETED_AT."', ".$column->getLength();
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
            return $this->decorator->decorate(ColumnModifier::DEFAULT, $default);
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

        $useTimestamps = true;
        foreach ($timestampsColumns as $timestamp) {
            if ($timestamp->getNotnull() || $timestamp->getDefault() !== null) {
                $useTimestamps = false;
            }
        }
        return $useTimestamps;
    }
}
