<?php

namespace KitLoong\MigrationsGenerator\Database\Models\PgSQL;

use KitLoong\MigrationsGenerator\Database\DatabaseColumnType;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;

class PgSQLColumnType extends DatabaseColumnType
{
    /**
     * @var array<string, \KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType>
     */
    protected static array $map = [
        '_text'            => ColumnType::TEXT,
        '_varchar'         => ColumnType::STRING,
        'bigint'           => ColumnType::BIG_INTEGER,
        'bigserial'        => ColumnType::BIG_INTEGER,
        'bool'             => ColumnType::BOOLEAN,
        'boolean'          => ColumnType::BOOLEAN,
        'bpchar'           => ColumnType::CHAR,
        'bytea'            => ColumnType::BINARY,
        'char'             => ColumnType::CHAR,
        'date'             => ColumnType::DATE,
        'datetime'         => ColumnType::DATETIME,
        'decimal'          => ColumnType::DECIMAL,
        'double precision' => ColumnType::FLOAT,
        'double'           => ColumnType::FLOAT,
        'float'            => ColumnType::FLOAT,
        'float4'           => ColumnType::FLOAT,
        'float8'           => ColumnType::FLOAT,
        'geography'        => ColumnType::GEOGRAPHY,
        'geometry'         => ColumnType::GEOMETRY,
        'inet'             => ColumnType::IP_ADDRESS,
        'int'              => ColumnType::INTEGER,
        'int2'             => ColumnType::SMALL_INTEGER,
        'int4'             => ColumnType::INTEGER,
        'int8'             => ColumnType::BIG_INTEGER,
        'integer'          => ColumnType::INTEGER,
        'interval'         => ColumnType::STRING,
        'json'             => ColumnType::JSON,
        'jsonb'            => ColumnType::JSONB,
        'macaddr'          => ColumnType::MAC_ADDRESS,
        'money'            => ColumnType::DECIMAL,
        'numeric'          => ColumnType::DECIMAL,
        'real'             => ColumnType::FLOAT,
        'serial'           => ColumnType::INTEGER,
        'serial4'          => ColumnType::INTEGER,
        'serial8'          => ColumnType::BIG_INTEGER,
        'smallint'         => ColumnType::SMALL_INTEGER,
        'text'             => ColumnType::TEXT,
        'time'             => ColumnType::TIME,
        'timestamp'        => ColumnType::TIMESTAMP,
        'timestamptz'      => ColumnType::TIMESTAMP_TZ,
        'timetz'           => ColumnType::TIME_TZ,
        'tsvector'         => ColumnType::TEXT,
        'uuid'             => ColumnType::UUID,
        'varchar'          => ColumnType::STRING,
        'year'             => ColumnType::INTEGER,
    ];

    /**
     * @inheritDoc
     */
    public static function toColumnType(string $dbType): ColumnType
    {
        return self::mapToColumnType(self::$map, $dbType);
    }
}
