<?php

namespace MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Collection;
use MigrationsGenerator\DBAL\Types\DBALTypes;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\Columns\DatetimeColumn;
use MigrationsGenerator\Generators\Columns\DecimalColumn;
use MigrationsGenerator\Generators\Columns\DoubleColumn;
use MigrationsGenerator\Generators\Columns\EnumColumn;
use MigrationsGenerator\Generators\Columns\FloatColumn;
use MigrationsGenerator\Generators\Columns\GeometryColumn;
use MigrationsGenerator\Generators\Columns\IntegerColumn;
use MigrationsGenerator\Generators\Columns\MiscColumn;
use MigrationsGenerator\Generators\Columns\SetColumn;
use MigrationsGenerator\Generators\Columns\StringColumn;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;
use MigrationsGenerator\Generators\Modifier\CharsetModifier;
use MigrationsGenerator\Generators\Modifier\CollationModifier;
use MigrationsGenerator\Generators\Modifier\CommentModifier;
use MigrationsGenerator\Generators\Modifier\DefaultModifier;
use MigrationsGenerator\Generators\Modifier\IndexModifier;
use MigrationsGenerator\Generators\Modifier\NullableModifier;

class ColumnGenerator
{
    private $datetimeColumn;
    private $decimalColumn;
    private $doubleColumn;
    private $enumColumn;
    private $floatColumn;
    private $geometryColumn;
    private $integerColumn;
    private $miscColumn;
    private $setColumn;
    private $stringColumn;
    private $charsetModifier;
    private $commentModifier;
    private $collationModifier;
    private $defaultModifier;
    private $indexModifier;
    private $nullableModifier;

    public function __construct(
        DatetimeColumn $datetimeColumn,
        DecimalColumn $decimalColumn,
        DoubleColumn $doubleColumn,
        EnumColumn $enumColumn,
        FloatColumn $floatColumn,
        GeometryColumn $geometryColumn,
        IntegerColumn $integerColumn,
        MiscColumn $miscColumn,
        SetColumn $setColumn,
        StringColumn $stringColumn,
        CharsetModifier $charsetModifier,
        CommentModifier $commentModifier,
        CollationModifier $collationModifier,
        DefaultModifier $defaultModifier,
        IndexModifier $indexModifier,
        NullableModifier $nullableModifier
    ) {
        $this->datetimeColumn    = $datetimeColumn;
        $this->decimalColumn     = $decimalColumn;
        $this->doubleColumn      = $doubleColumn;
        $this->enumColumn        = $enumColumn;
        $this->floatColumn       = $floatColumn;
        $this->geometryColumn    = $geometryColumn;
        $this->integerColumn     = $integerColumn;
        $this->miscColumn        = $miscColumn;
        $this->setColumn         = $setColumn;
        $this->stringColumn      = $stringColumn;
        $this->charsetModifier   = $charsetModifier;
        $this->commentModifier   = $commentModifier;
        $this->collationModifier = $collationModifier;
        $this->defaultModifier   = $defaultModifier;
        $this->indexModifier     = $indexModifier;
        $this->nullableModifier  = $nullableModifier;
    }

    /**
     * Converts column into migration column method.
     *
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @param  \Illuminate\Support\Collection<string, \Doctrine\DBAL\Schema\Index>  $singleColumnIndexes
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function generate(Table $table, Column $column, Collection $singleColumnIndexes): Method
    {
        $type = $this->mapToColumnType($column->getType()->getName());

        // Generate method with given $type.
        // For example: TINYINT(1) will be changed into BOOLEAN.
        // Both old and new $type will be set into $method->name.
        switch ($type) {
            case ColumnType::INTEGER:
            case ColumnType::BIG_INTEGER:
            case ColumnType::MEDIUM_INTEGER:
            case ColumnType::SMALL_INTEGER:
            case ColumnType::TINY_INTEGER:
                $method = $this->integerColumn->generate($type, $table, $column);
                break;
            case ColumnType::DATE:
            case ColumnType::DATETIME:
            case ColumnType::DATETIME_TZ:
            case ColumnType::TIME:
            case ColumnType::TIME_TZ:
            case ColumnType::TIMESTAMP:
            case ColumnType::TIMESTAMP_TZ:
                $method = $this->datetimeColumn->generate($type, $table, $column);
                break;
            case ColumnType::DECIMAL:
                $method = $this->decimalColumn->generate($type, $table, $column);
                break;
            case ColumnType::FLOAT:
                $method = $this->floatColumn->generate($type, $table, $column);
                break;
            case ColumnType::DOUBLE:
                $method = $this->doubleColumn->generate($type, $table, $column);
                break;
            case ColumnType::ENUM:
                $method = $this->enumColumn->generate($type, $table, $column);
                break;
            case ColumnType::GEOMETRY:
                $method = $this->geometryColumn->generate($type, $table, $column);
                break;
            case ColumnType::SET:
                $method = $this->setColumn->generate($type, $table, $column);
                break;
            case ColumnType::STRING:
                $method = $this->stringColumn->generate($type, $table, $column);
                break;
            default:
                $method = $this->miscColumn->generate($type, $table, $column);
        }

        // $type may be changed after above `generate` operation, and the new type is stored as method name.
        // Refresh $type by get method name.
        $type = $method->getName();

        $method = $this->charsetModifier->chainCharset($table, $method, $column);
        $method = $this->collationModifier->chainCollation($table, $method, $column);
        $method = $this->nullableModifier->chainNullable($method, $type, $column);
        $method = $this->defaultModifier->chainDefault($table, $method, $type, $column);
        $method = $this->indexModifier->chainIndex($table, $method, $singleColumnIndexes, $column);
        $method = $this->commentModifier->chainComment($method, $column);

        return $method;
    }

    /**
     * Converts built-in DBALTypes to ColumnType (Laravel column).
     *
     * @param  string  $dbalType
     * @return string
     */
    private function mapToColumnType(string $dbalType): string
    {
        $map = [
            DBALTypes::BIGINT               => ColumnType::BIG_INTEGER,
            DBALTypes::BLOB                 => ColumnType::BINARY,
            DBALTypes::DATE_MUTABLE         => ColumnType::DATE,
            DBALTypes::DATE_IMMUTABLE       => ColumnType::DATE,
            DBALTypes::DATETIME_MUTABLE     => ColumnType::DATETIME,
            DBALTypes::DATETIME_IMMUTABLE   => ColumnType::DATETIME,
            DBALTypes::DATETIMETZ_MUTABLE   => ColumnType::DATETIME_TZ,
            DBALTypes::DATETIMETZ_IMMUTABLE => ColumnType::DATETIME_TZ,
            DBALTypes::SMALLINT             => ColumnType::SMALL_INTEGER,
            DBALTypes::GUID                 => ColumnType::UUID,
            DBALTypes::TIME_MUTABLE         => ColumnType::TIME,
            DBALTypes::TIME_IMMUTABLE       => ColumnType::TIME,
        ];
        return $map[$dbalType] ?? $dbalType; // $dbalType outside from the map has same name with ColumnType.
    }
}
