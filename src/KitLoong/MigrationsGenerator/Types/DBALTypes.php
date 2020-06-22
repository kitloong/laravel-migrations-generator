<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/01
 */

namespace KitLoong\MigrationsGenerator\Types;

use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

final class DBALTypes
{
    /**
     * @see \Doctrine\DBAL\Types\Types::BIGINT
     */
    const BIGINT = 'bigint';

    /**
     * @see \Doctrine\DBAL\Types\Types::BLOB
     */
    const BLOB = 'blob';

    /**
     * @see \Doctrine\DBAL\Types\Types::BOOLEAN
     */
    const BOOLEAN = 'boolean';

    /**
     * @see \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE
     */
    const DATETIME_MUTABLE = 'datetime';

    /**
     * @see \Doctrine\DBAL\Types\Types::DECIMAL
     */
    const DECIMAL = 'decimal';

    /**
     * @see \Doctrine\DBAL\Types\Types::FLOAT
     */
    const FLOAT = 'float';

    /**
     * @see \Doctrine\DBAL\Types\Types::INTEGER
     */
    const INTEGER = 'integer';

    /**
     * @see \Doctrine\DBAL\Types\Types::SMALLINT
     */
    const SMALLINT = 'smallint';

    /**
     * @see \Doctrine\DBAL\Types\Types::STRING
     */
    const STRING = 'string';

    /**
     * @see \Doctrine\DBAL\Types\Types::TIME_MUTABLE
     */
    const TIME_MUTABLE = 'time';

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
}
