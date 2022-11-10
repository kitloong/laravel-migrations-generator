<?php

namespace KitLoong\MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Types\AsciiStringType;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\BinaryType;
use Doctrine\DBAL\Types\BlobType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\DateIntervalType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzImmutableType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\SimpleArrayType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\TimeImmutableType;
use Doctrine\DBAL\Types\TimeType;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;

final class Types
{
    /**
     * Default built-in types provided by Doctrine DBAL.
     */
    public const BUILTIN_TYPES_MAP = [
        AsciiStringType::class         => ColumnType::STRING,
        BigIntType::class              => ColumnType::BIG_INTEGER,
        BinaryType::class              => ColumnType::BINARY,
        BlobType::class                => ColumnType::BINARY,
        BooleanType::class             => ColumnType::BOOLEAN,
        DateType::class                => ColumnType::DATE,
        DateImmutableType::class       => ColumnType::DATE,
        DateIntervalType::class        => ColumnType::DATE,
        DateTimeType::class            => ColumnType::DATETIME,
        DateTimeImmutableType::class   => ColumnType::DATETIME,
        DateTimeTzType::class          => ColumnType::DATETIME_TZ,
        DateTimeTzImmutableType::class => ColumnType::DATETIME_TZ,
        DecimalType::class             => ColumnType::DECIMAL,
        FloatType::class               => ColumnType::FLOAT,
        GuidType::class                => ColumnType::UUID,
        IntegerType::class             => ColumnType::INTEGER,
        JsonType::class                => ColumnType::JSON,
        SimpleArrayType::class         => ColumnType::STRING,
        SmallIntType::class            => ColumnType::SMALL_INTEGER,
        StringType::class              => ColumnType::STRING,
        TextType::class                => ColumnType::TEXT,
        TimeType::class                => ColumnType::TIME,
        TimeImmutableType::class       => ColumnType::TIME,
    ];

    /**
     * Additional types provided by Migration Generator.
     */
    public const ADDITIONAL_TYPES_MAP = [
        DoubleType::class             => ColumnType::DOUBLE,
        EnumType::class               => ColumnType::ENUM,
        GeometryType::class           => ColumnType::GEOMETRY,
        GeometryCollectionType::class => ColumnType::GEOMETRY_COLLECTION,
        IpAddressType::class          => ColumnType::IP_ADDRESS,
        JsonbType::class              => ColumnType::JSONB,
        LineStringType::class         => ColumnType::LINE_STRING,
        LongTextType::class           => ColumnType::LONG_TEXT,
        MacAddressType::class         => ColumnType::MAC_ADDRESS,
        MediumIntegerType::class      => ColumnType::MEDIUM_INTEGER,
        MediumTextType::class         => ColumnType::MEDIUM_TEXT,
        MultiLineStringType::class    => ColumnType::MULTI_LINE_STRING,
        MultiPointType::class         => ColumnType::MULTI_POINT,
        MultiPolygonType::class       => ColumnType::MULTI_POLYGON,
        PointType::class              => ColumnType::POINT,
        PolygonType::class            => ColumnType::POLYGON,
        SetType::class                => ColumnType::SET,
        TimestampType::class          => ColumnType::TIMESTAMP,
        TimestampTzType::class        => ColumnType::TIMESTAMP_TZ,
        TimeTzType::class             => ColumnType::TIME_TZ,
        TinyIntegerType::class        => ColumnType::TINY_INTEGER,
        UUIDType::class               => ColumnType::UUID,
        YearType::class               => ColumnType::YEAR,
    ];
}
