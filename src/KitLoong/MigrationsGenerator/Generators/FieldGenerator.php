<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/28
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Generators\Modifier\CommentModifier;
use KitLoong\MigrationsGenerator\Generators\Modifier\DefaultModifier;
use KitLoong\MigrationsGenerator\Generators\Modifier\IndexModifier;
use KitLoong\MigrationsGenerator\Generators\Modifier\NullableModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\Support\CheckLaravelVersion;
use KitLoong\MigrationsGenerator\Types\DBALTypes;

class FieldGenerator
{
    use CheckLaravelVersion;

    private $decorator;
    private $integerField;
    private $datetimeField;
    private $decimalField;
    private $geometryField;
    private $stringField;
    private $enumField;
    private $setField;
    private $otherField;
    private $nullableModifier;
    private $defaultModifier;
    private $indexModifier;
    private $commentModifier;

    public function __construct(
        Decorator $decorator,
        IntegerField $integerField,
        DatetimeField $datetimeField,
        DecimalField $decimalField,
        GeometryField $geometryField,
        StringField $stringField,
        EnumField $enumField,
        SetField $setField,
        OtherField $otherField,
        NullableModifier $nullableModifier,
        DefaultModifier $defaultModifier,
        IndexModifier $indexModifier,
        CommentModifier $commentModifier
    ) {
        $this->decorator = $decorator;
        $this->integerField = $integerField;
        $this->datetimeField = $datetimeField;
        $this->decimalField = $decimalField;
        $this->geometryField = $geometryField;
        $this->stringField = $stringField;
        $this->enumField = $enumField;
        $this->setField = $setField;
        $this->otherField = $otherField;
        $this->nullableModifier = $nullableModifier;
        $this->defaultModifier = $defaultModifier;
        $this->indexModifier = $indexModifier;
        $this->commentModifier = $commentModifier;
    }

    /**
     * Convert dbal types to Laravel Migration Types
     * @var array
     */
    public static $fieldTypeMap = [
        DBALTypes::SMALLINT => ColumnType::SMALL_INTEGER,
        DBALTypes::BIGINT => ColumnType::BIG_INTEGER,
        DBALTypes::DATETIME_MUTABLE => ColumnType::DATETIME,
        DBALTypes::DATETIME_IMMUTABLE => ColumnType::DATETIME,
        DBALTypes::DATETIMETZ_MUTABLE => ColumnType::DATETIME_TZ,
        DBALTypes::DATETIMETZ_IMMUTABLE => ColumnType::DATETIME_TZ,
        DBALTypes::BLOB => ColumnType::BINARY,
        DBALTypes::GUID => ColumnType::UUID,
    ];

    /**
     * @param  string  $table
     * @param  Column[]  $columns
     * @param  Collection  $indexes
     * @return array
     */
    public function generate(string $table, $columns, Collection $indexes): array
    {
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

            $field = $this->makeLaravelFieldTypeMethod($table, $field, $column, $indexes, $useTimestamps);

            if (empty($field)) {
                continue;
            }

            if (!$column->getNotnull()) {
                if ($this->nullableModifier->shouldAddNullableModifier($field['type'])) {
                    $field['decorators'][] = ColumnModifier::NULLABLE;
                }
            }

            if ($column->getDefault() !== null) {
                $field['decorators'][] = $this->defaultModifier->generate($dbalType, $column);
            }

            if ($indexes->has($field['field'])) {
                $field['decorators'][] = $this->indexModifier->generate($indexes->get($field['field']));
            }

            if ($column->getComment() !== null) {
                $field['decorators'][] = $this->commentModifier->generate($column->getComment());
            }

            if (!$this->atLeastLaravel8()) {
                if ($field['type'] === DBALTypes::TIMESTAMP) {
                    if (($key1 = array_search(ColumnModifier::USE_CURRENT, $field['decorators'])) !== false &&
                        ($key2 = array_search(ColumnModifier::USE_CURRENT_ON_UPDATE, $field['decorators'])) !== false) {
                        unset($field['decorators'][$key1]);
                        unset($field['decorators'][$key2]);
                    }
                }
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
            case DBALTypes::INTEGER:
            case DBALTypes::BIGINT:
            case DBALTypes::MEDIUMINT:
            case DBALTypes::SMALLINT:
            case DBALTypes::TINYINT:
                return $this->integerField->makeField($tableName, $field, $column, $indexes);
            case DBALTypes::DATETIME_MUTABLE:
            case DBALTypes::DATETIME_IMMUTABLE:
            case DBALTypes::DATETIMETZ_MUTABLE:
            case DBALTypes::DATETIMETZ_IMMUTABLE:
            case DBALTypes::TIMESTAMP:
            case DBALTypes::TIMESTAMP_TZ:
            case DBALTypes::TIME_MUTABLE:
            case DBALTypes::TIME_IMMUTABLE:
            case DBALTypes::TIME_TZ:
                return $this->datetimeField->makeField($tableName, $field, $column, $useTimestamps);
            case DBALTypes::DECIMAL:
            case DBALTypes::FLOAT:
            case DBALTypes::DOUBLE:
                return $this->decimalField->makeField($field, $column);
            case DBALTypes::ENUM:
                return $this->enumField->makeField($tableName, $field, $column);
            case DBALTypes::GEOMETRY:
                return $this->geometryField->makeField($tableName, $field);
            case DBALTypes::SET:
                return $this->setField->makeField($tableName, $field, $column);
            case DBALTypes::STRING:
                return $this->stringField->makeField($tableName, $field, $column);
            default:
                return $this->otherField->makeField($tableName, $field, $column);
        }
    }
}
