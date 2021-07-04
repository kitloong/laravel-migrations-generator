<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/01
 */

namespace KitLoong\MigrationsGenerator\Types;

use Doctrine\DBAL\Types\Types;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

final class DBALTypes
{
    const BIGINT = Types::BIGINT;
    const BLOB = Types::BLOB;
    const BOOLEAN = Types::BOOLEAN;
    const DATETIME_MUTABLE = Types::DATETIME_MUTABLE;
    const DATETIME_IMMUTABLE = Types::DATETIME_IMMUTABLE;
    const DATETIMETZ_MUTABLE = Types::DATETIMETZ_MUTABLE;
    const DATETIMETZ_IMMUTABLE = Types::DATETIMETZ_IMMUTABLE;
    const DATE = Types::DATETIMETZ_MUTABLE;
    const DECIMAL = Types::DECIMAL;
    const FLOAT = Types::FLOAT;
    const GUID = Types::GUID;
    const INTEGER = Types::INTEGER;
    const SMALLINT = Types::SMALLINT;
    const STRING = Types::STRING;
    const TIME_MUTABLE = Types::TIME_MUTABLE;
    const TIME_IMMUTABLE = Types::TIME_IMMUTABLE;

    // Custom types, should identical with CustomDoctrineType name

    /**
     * @see DoubleType::getName()
     */
    const DOUBLE = ColumnType::DOUBLE;

    /**
     * @see EnumType::getName()
     */
    const ENUM = ColumnType::ENUM;

    /**
     * @see GeometryType::getName()
     * @see GeographyType::getName()
     */
    const GEOMETRY = ColumnType::GEOMETRY;

    /**
     * @see MediumIntegerType::getName()
     */
    const MEDIUMINT = ColumnType::MEDIUM_INTEGER;

    /**
     * @see SetType::getName()
     */
    const SET = ColumnType::SET;

    /**
     * @see TimestampType::getName()
     */
    const TIMESTAMP = ColumnType::TIMESTAMP;

    /**
     * @see TinyIntegerType::getName()
     */
    const TINYINT = ColumnType::TINY_INTEGER;

    /**
     * @see TimeTzType::getName()
     */
    const TIME_TZ = ColumnType::TIME_TZ;

    /**
     * @see TimestampTzType::getName()
     */
    const TIMESTAMP_TZ = ColumnType::TIMESTAMP_TZ;
}
