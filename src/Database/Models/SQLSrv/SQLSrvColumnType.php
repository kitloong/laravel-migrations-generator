<?php

namespace KitLoong\MigrationsGenerator\Database\Models\SQLSrv;

use KitLoong\MigrationsGenerator\Database\DatabaseColumnType;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;

class SQLSrvColumnType extends DatabaseColumnType
{
    /**
     * @var array<string, \KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType>
     */
    protected static array $map = [
        'bigint'           => ColumnType::BIG_INTEGER,
        'binary'           => ColumnType::BINARY,
        'bit'              => ColumnType::BOOLEAN,
        'blob'             => ColumnType::BINARY,
        'char'             => ColumnType::STRING,
        'date'             => ColumnType::DATE,
        'datetime'         => ColumnType::DATETIME,
        'datetime2'        => ColumnType::DATETIME,
        'datetimeoffset'   => ColumnType::DATETIME_TZ,
        'decimal'          => ColumnType::DECIMAL,
        'double precision' => ColumnType::FLOAT,
        'double'           => ColumnType::FLOAT,
        'float'            => ColumnType::FLOAT,
        'geography'        => ColumnType::GEOGRAPHY,
        'geometry'         => ColumnType::GEOMETRY,
        'image'            => ColumnType::BINARY,
        'int'              => ColumnType::INTEGER,
        'money'            => ColumnType::DECIMAL,
        'nchar'            => ColumnType::CHAR,
        'ntext'            => ColumnType::TEXT,
        'numeric'          => ColumnType::DECIMAL,
        'nvarchar'         => ColumnType::STRING,
        'real'             => ColumnType::FLOAT,
        'smalldatetime'    => ColumnType::DATETIME,
        'smallint'         => ColumnType::SMALL_INTEGER,
        'smallmoney'       => ColumnType::DECIMAL,
        'text'             => ColumnType::TEXT,
        'time'             => ColumnType::TIME,
        'tinyint'          => ColumnType::TINY_INTEGER,
        'uniqueidentifier' => ColumnType::UUID,
        'varbinary'        => ColumnType::BINARY,
        'varchar'          => ColumnType::STRING,
        'xml'              => ColumnType::TEXT,
    ];

    /**
     * @inheritDoc
     */
    public static function toColumnType(string $dbType): ColumnType
    {
        return self::mapToColumnType(self::$map, $dbType);
    }
}
