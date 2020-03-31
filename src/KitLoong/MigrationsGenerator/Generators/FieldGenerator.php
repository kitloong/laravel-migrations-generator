<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/28
 * Time: 20:29
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

class FieldGenerator
{
    private $decorator;
    private $integerField;
    private $datetimeField;
    private $decimalField;
    private $stringField;
    private $enumField;
    private $setField;
    private $otherField;

    public function __construct(
        Decorator $decorator,
        IntegerField $integerField,
        DatetimeField $datetimeField,
        DecimalField $decimalField,
        StringField $stringField,
        EnumField $enumField,
        SetField $setField,
        OtherField $otherField
    ) {
        $this->decorator = $decorator;
        $this->integerField = $integerField;
        $this->datetimeField = $datetimeField;
        $this->decimalField = $decimalField;
        $this->stringField = $stringField;
        $this->enumField = $enumField;
        $this->setField = $setField;
        $this->otherField = $otherField;
    }

    /**
     * Convert dbal types to Laravel Migration Types
     * @var array
     */
    public static $fieldTypeMap = [
        'tinyint' => ColumnType::TINY_INTEGER,
        'smallint' => ColumnType::SMALL_INTEGER,
        'mediumint' => ColumnType::MEDIUM_INTEGER,
        'bigint' => ColumnType::BIG_INTEGER,
        'datetime' => ColumnType::DATETIME,
        'blob' => ColumnType::BINARY,
    ];

    /**
     * @param  Table  $table
     * @param  Collection  $indexes
     * @return array
     */
    public function generate(Table $table, Collection $indexes): array
    {
        $columns = $table->getColumns();
        if (count($columns) === 0) {
            return [];
        }

        $useTimestamps = $this->datetimeField->isUseTimestamps($columns);

        $fields = [];

        foreach ($columns as $column) {
            /**
             * return [
             *  field : Field name,
             *  type  : Migration type method, eg: increments, string
             *  args  : Migration type arguments,
             *      eg: decimal('amount', 8, 2) => decimal('amount', args[0], args[1])
             *  decorators
             * ]
             */
            $dbalType = $column->getType()->getName();

            $field = [
                'field' => $this->decorator->addSlash($column->getName()),
                'type' => $dbalType,
                'args' => [],
                'decorators' => []
            ];

            $field = $this->makeLaravelFieldTypeMethod($table->getName(), $field, $column, $indexes, $useTimestamps);

            if (empty($field)) {
                continue;
            }

            if (!$column->getNotnull()) {
                if ($this->shouldAddNullableModifier($field['type'])) {
                    $field['decorators'][] = ColumnModifier::NULLABLE;
                }
            }

            if ($column->getDefault() !== null) {
                $field['decorators'][] = $this->decorateDefault($dbalType, $column);
            }

            if ($indexes->has($field['field'])) {
                $field['decorators'][] = $this->decorateIndex($indexes->get($field['field']));
            }

            if ($column->getComment() !== null) {
                $field['decorators'][] = $this->decorateComment($column->getComment());
            }

            $fields[] = $field;
        }
        return $fields;
    }

    /**
     * @param  string  $tableName
     * @param  array  $field
     * @param  Column  $column
     * @param  Collection  $indexes
     * @param  bool  $useTimestamps
     * @return array
     */
    private function makeLaravelFieldTypeMethod(
        string $tableName,
        array $field,
        Column $column,
        Collection $indexes,
        bool $useTimestamps
    ): array {
        switch ($field['type']) {
            case Types::SMALLINT:
            case Types::INTEGER:
            case Types::BIGINT:
            case 'mediumint':
                return $this->integerField->makeField($field, $column, $indexes);
            case Types::DATETIME_MUTABLE:
                return $this->datetimeField->makeField($field, $column, $useTimestamps);
            case Types::DECIMAL:
            case Types::FLOAT:
            case 'double':
                return $this->decimalField->makeField($field, $column);
            case 'enum':
                return $this->enumField->makeField($tableName, $field);
            case 'set':
                return $this->setField->makeField($tableName, $field);
            case Types::STRING:
                return $this->stringField->makeField($field, $column);
            default:
                return $this->otherField->makeField($field);
        }
    }

    /**
     * @param  string  $dbalType
     * @param  Column  $column
     * @return string
     */
    private function decorateDefault(string $dbalType, Column $column): string
    {
        switch ($dbalType) {
            case Types::SMALLINT:
            case Types::INTEGER:
            case Types::BIGINT:
            case 'mediumint':
            case Types::DECIMAL:
            case Types::FLOAT:
            case 'double':
                $default = $column->getDefault();
                break;
            case Types::DATETIME_MUTABLE:
                return $this->datetimeField->makeDefault($column);
            default:
                $default = $this->decorator->columnDefaultToString($column->getDefault());
        }

        return $this->decorator->decorate(ColumnModifier::DEFAULT, [$default]);
    }

    private function decorateComment(string $comment): string
    {
        return $this->decorator->decorate(
            ColumnModifier::COMMENT,
            ["'".$this->decorator->addSlash($comment)."'"]
        );
    }

    private function decorateIndex(array $index): string
    {
        return $this->decorator->decorate(
            $index['type'],
            // $index['args'] is wrapped with '
            (!empty($index['args'][0]) ? [$index['args'][0]] : [])
        );
    }

    private function shouldAddNullableModifier(string $type): bool
    {
        return !in_array($type, [ColumnType::SOFT_DELETES, ColumnType::REMEMBER_TOKEN, ColumnType::TIMESTAMPS]);
    }
}
