<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

class IntegerField
{
    public function makeField(string $tableName, array $field, Column $column, Collection $indexes): array
    {
        if (isset(FieldGenerator::$fieldTypeMap[$field['type']])) {
            $field['type'] = FieldGenerator::$fieldTypeMap[$field['type']];
        }

        $isBoolean = $this->checkIsMySQLBoolean($tableName, $field, $column);
        if ($isBoolean) {
            return $this->handleBoolean($field, $column);
        } else {
            return $this->handleInteger($field, $column, $indexes);
        }
    }

    private function handleBoolean(array $field, Column $column): array
    {
        $field['type'] = ColumnType::BOOLEAN;
        if ($column->getUnsigned()) {
            $field['decorators'][] = ColumnModifier::UNSIGNED;
        }

        return $field;
    }

    private function handleInteger(array $field, Column $column, Collection $indexes): array
    {
        if ($column->getUnsigned() && $column->getAutoincrement()) {
            if ($field['type'] === 'integer') {
                $field['type'] = ColumnType::INCREMENTS;
            } else {
                $field['type'] = str_replace('Integer', 'Increments', $field['type']);
            }

            $indexes->forget($field['field']);
        } else {
            if ($column->getUnsigned()) {
                $field['type'] = 'unsigned'.ucfirst($field['type']);
            }
            if ($column->getAutoincrement()) {
                $field['args'][] = 'true';
                $indexes->forget($field['field']);
            }
        }
        return $field;
    }

    private function checkIsMySQLBoolean(string $tableName, array $field, Column $column): bool
    {
        /** @var MigrationsGeneratorSetting $setting */
        $setting = app(MigrationsGeneratorSetting::class);

        if ($setting->getPlatform() === Platform::MYSQL &&
            $field['type'] === ColumnType::TINY_INTEGER &&
            !$column->getAutoincrement()) {
            $column = $setting->getConnection()->select("SHOW COLUMNS FROM `${tableName}` where Field = '${field['field']}' AND Type LIKE 'tinyint(1)%'");
            return !empty($column);
        }
        return false;
    }
}
