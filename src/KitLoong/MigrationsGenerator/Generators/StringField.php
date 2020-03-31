<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 * Time: 14:56
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\Schema\Builder;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnName;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

class StringField
{
    public function makeField(array $field, Column $column): array
    {
        if ($column->getFixed()) {
            $field['type'] = ColumnType::CHAR;
        } else {
            if ($column->getLength()) {
                if ($column->getLength() !== Builder::$defaultStringLength) {
                    $field['args'] = $column->getLength();
                }

                if ($field['field'] === ColumnName::REMEMBER_TOKEN && $column->getLength() === 100) {
                    $field['type'] = ColumnType::REMEMBER_TOKEN;
                    $field['field'] = null;
                    $field['args'] = null;
                }
            }
        }
        return $field;
    }
}
