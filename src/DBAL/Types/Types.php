<?php

namespace KitLoong\MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Types\Types as BuiltInTypes;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;

final class Types
{
    // Default built-in types provided by Doctrine DBAL.
    public const ARRAY                = BuiltInTypes::ARRAY;
    public const ASCII_STRING         = BuiltInTypes::ASCII_STRING;
    public const BIGINT               = BuiltInTypes::BIGINT;
    public const BINARY               = BuiltInTypes::BINARY;
    public const BLOB                 = BuiltInTypes::BLOB;
    public const BOOLEAN              = BuiltInTypes::BOOLEAN;
    public const DATE_MUTABLE         = BuiltInTypes::DATE_MUTABLE;
    public const DATE_IMMUTABLE       = BuiltInTypes::DATE_IMMUTABLE;
    public const DATEINTERVAL         = BuiltInTypes::DATEINTERVAL;
    public const DATETIME_MUTABLE     = BuiltInTypes::DATETIME_MUTABLE;
    public const DATETIME_IMMUTABLE   = BuiltInTypes::DATETIME_IMMUTABLE;
    public const DATETIMETZ_MUTABLE   = BuiltInTypes::DATETIMETZ_MUTABLE;
    public const DATETIMETZ_IMMUTABLE = BuiltInTypes::DATETIMETZ_IMMUTABLE;
    public const DECIMAL              = BuiltInTypes::DECIMAL;
    public const FLOAT                = BuiltInTypes::FLOAT;
    public const GUID                 = BuiltInTypes::GUID;
    public const INTEGER              = BuiltInTypes::INTEGER;
    public const JSON                 = BuiltInTypes::JSON;
    public const OBJECT               = BuiltInTypes::OBJECT;
    public const SIMPLE_ARRAY         = BuiltInTypes::SIMPLE_ARRAY;
    public const SMALLINT             = BuiltInTypes::SMALLINT;
    public const STRING               = BuiltInTypes::STRING;
    public const TEXT                 = BuiltInTypes::TEXT;
    public const TIME_MUTABLE         = BuiltInTypes::TIME_MUTABLE;
    public const TIME_IMMUTABLE       = BuiltInTypes::TIME_IMMUTABLE;

    /**
     * Custom types, should identical with CustomDoctrineType name.
     * Example,
     *
     * @see \KitLoong\MigrationsGenerator\DBAL\Types\DoubleType::getName()
     */
    public const DOUBLE              = ColumnType::DOUBLE;
    public const ENUM                = ColumnType::ENUM;
    public const GEOMETRY            = ColumnType::GEOMETRY;
    public const GEOMETRY_COLLECTION = ColumnType::GEOMETRY_COLLECTION;
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
