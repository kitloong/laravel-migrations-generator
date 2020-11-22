<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\Schema\Builder;
use KitLoong\MigrationsGenerator\Generators\Modifier\CollationModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnName;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

class StringField
{
    private $collationModifier;

    public function __construct(CollationModifier $collationModifier)
    {
        $this->collationModifier = $collationModifier;
    }

    public function makeField(string $tableName, array $field, Column $column): array
    {
        if ($field['field'] === ColumnName::REMEMBER_TOKEN && $column->getLength() === 100 && !$column->getFixed()) {
            $field['type'] = ColumnType::REMEMBER_TOKEN;
            $field['field'] = null;
            $field['args'] = [];
        } else {
            if ($column->getFixed()) {
                $field['type'] = ColumnType::CHAR;
            }

            if ($column->getLength() && $column->getLength() !== Builder::$defaultStringLength) {
                $field['args'][] = $column->getLength();
            }
        }

        $collation = $this->collationModifier->generate($tableName, $column);
        if ($collation !== '') {
            $field['decorators'][] = $collation;
        }

        return $field;
    }
}
