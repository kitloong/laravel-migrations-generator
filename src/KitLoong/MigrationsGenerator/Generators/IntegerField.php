<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 * Time: 12:50
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

class IntegerField
{
    public function makeField(array $field, Column $column, Collection $indexes): array
    {
        if ($column->getUnsigned() && $column->getAutoincrement()) {
            if ($field['type'] === 'integer') {
                $field['type'] = ColumnType::INCREMENTS;
            } else {
                $field['type'] = str_replace('int', 'Increments', $field['type']);
            }

            $indexes->forget($field['field']);
        } else {
            if (isset(FieldGenerator::$fieldTypeMap[$field['type']])) {
                $field['type'] = FieldGenerator::$fieldTypeMap[$field['type']];
            }
            if ($column->getUnsigned()) {
                $field['decorators'][] = ColumnModifier::UNSIGNED;
            }
            if ($column->getAutoincrement()) {
                $field['args'] = 'true';
                $indexes->forget($field['field']);
            }
        }

        return $field;
    }
}
