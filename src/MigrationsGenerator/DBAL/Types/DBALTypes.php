<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Types\Types;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;

final class DBALTypes
{
    // Default built-in types provided by Doctrine DBAL.
    public const ARRAY                = Types::ARRAY;
    public const ASCII_STRING         = Types::ASCII_STRING;
    public const BIGINT               = Types::BIGINT;
    public const BINARY               = Types::BINARY;
    public const BLOB                 = Types::BLOB;
    public const BOOLEAN              = Types::BOOLEAN;
    public const DATE_MUTABLE         = Types::DATE_MUTABLE;
    public const DATE_IMMUTABLE       = Types::DATE_IMMUTABLE;
    public const DATEINTERVAL         = Types::DATEINTERVAL;
    public const DATETIME_MUTABLE     = Types::DATETIME_MUTABLE;
    public const DATETIME_IMMUTABLE   = Types::DATETIME_IMMUTABLE;
    public const DATETIMETZ_MUTABLE   = Types::DATETIMETZ_MUTABLE;
    public const DATETIMETZ_IMMUTABLE = Types::DATETIMETZ_IMMUTABLE;
    public const DECIMAL              = Types::DECIMAL;
    public const FLOAT                = Types::FLOAT;
    public const GUID                 = Types::GUID;
    public const INTEGER              = Types::INTEGER;
    public const JSON                 = Types::JSON;
    public const OBJECT               = Types::OBJECT;
    public const SIMPLE_ARRAY         = Types::SIMPLE_ARRAY;
    public const SMALLINT             = Types::SMALLINT;
    public const STRING               = Types::STRING;
    public const TEXT                 = Types::TEXT;
    public const TIME_MUTABLE         = Types::TIME_MUTABLE;
    public const TIME_IMMUTABLE       = Types::TIME_IMMUTABLE;

    /**
     * Custom types, should identical with CustomDoctrineType name.
     * Example,
     * @see \MigrationsGenerator\DBAL\Types\DoubleType::getName()
     */
    public const DOUBLE              = ColumnType::DOUBLE;
    public const ENUM                = ColumnType::ENUM;
    public const GEOMETRY_COLLECTION = ColumnType::GEOMETRY_COLLECTION;
    public const GEOMETRY            = ColumnType::GEOMETRY;
    public const IP_ADDRESS          = ColumnType::IP_ADDRESS;
    public const JSONB               = ColumnType::JSONB;
    public const LINE_STRING         = ColumnType::LINE_STRING;
    public const LONG_TEXT           = ColumnType::LONG_TEXT;
    public const MAC_ADDRESS         = ColumnType::MAC_ADDRESS;
    public const MEDIUM_INTEGER      = ColumnType::MEDIUM_INTEGER;
    public const MEDIUM_TEXT         = ColumnType::MEDIUM_TEXT;
    public const MULTI_LINE_STRING   = ColumnType::MULTI_LINE_STRING;
    public const MULTI_POINT         = ColumnType::MULTI_POINT;
    public const MULTI_POLYGON       = ColumnType::MULTI_POLYGON;
    public const POINT               = ColumnType::POINT;
    public const POLYGON             = ColumnType::POLYGON;
    public const SET                 = ColumnType::SET;
    public const TIMESTAMP           = ColumnType::TIMESTAMP;
    public const TIMESTAMP_TZ        = ColumnType::TIMESTAMP_TZ;
    public const TIME_TZ             = ColumnType::TIME_TZ;
    public const TINY_INTEGER        = ColumnType::TINY_INTEGER;
    public const UUID                = ColumnType::UUID;
    public const YEAR                = ColumnType::YEAR;
}
