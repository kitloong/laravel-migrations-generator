<?php

namespace KitLoong\MigrationsGenerator\Database\Models\SQLite;

use KitLoong\MigrationsGenerator\Database\DatabaseColumnType;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;

class SQLiteColumnType extends DatabaseColumnType
{
    /**
     * @var array<string, \KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType>
     */
    protected static array $map = [
        'bigint'           => ColumnType::BIG_INTEGER,
        'bigserial'        => ColumnType::BIG_INTEGER,
        'blob'             => ColumnType::BINARY,
        'boolean'          => ColumnType::BOOLEAN,
        'char'             => ColumnType::STRING,
        'clob'             => ColumnType::TEXT,
        'date'             => ColumnType::DATE,
        'datetime'         => ColumnType::DATETIME,
        'decimal'          => ColumnType::DECIMAL,
        'double'           => ColumnType::DOUBLE,
        'double precision' => ColumnType::DOUBLE,
        'float'            => ColumnType::FLOAT,
        'image'            => ColumnType::STRING,
        'int'              => ColumnType::INTEGER,
        'integer'          => ColumnType::INTEGER,
        'geometry'         => ColumnType::GEOMETRY,
        'longtext'         => ColumnType::TEXT,
        'longvarchar'      => ColumnType::STRING,
        'mediumint'        => ColumnType::INTEGER,
        'mediumtext'       => ColumnType::TEXT,
        'ntext'            => ColumnType::STRING,
        'numeric'          => ColumnType::DECIMAL,
        'nvarchar'         => ColumnType::STRING,
        'real'             => ColumnType::FLOAT,
        'serial'           => ColumnType::INTEGER,
        'smallint'         => ColumnType::INTEGER,
        'string'           => ColumnType::STRING,
        'text'             => ColumnType::TEXT,
        'time'             => ColumnType::TIME,
        'timestamp'        => ColumnType::DATETIME,
        'tinyint'          => ColumnType::INTEGER,
        'tinytext'         => ColumnType::TEXT,
        'varchar'          => ColumnType::STRING,
        'varchar2'         => ColumnType::STRING,
    ];

    /**
     * @inheritDoc
     */
    public static function toColumnType(string $dbType): ColumnType
    {
        return self::mapToColumnType(self::$map, $dbType);
    }
}
