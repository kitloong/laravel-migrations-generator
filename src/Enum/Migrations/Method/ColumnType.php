<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

use Doctrine\DBAL\Types\Type;
use KitLoong\MigrationsGenerator\DBAL\Types\Types;
use MyCLabs\Enum\Enum;

/**
 * Define column types of the framework.
 * Keep const as public to allow used by:
 * {@see \KitLoong\MigrationsGenerator\DBAL\RegisterColumnType::registerLaravelColumnType()}
 * {@see \KitLoong\MigrationsGenerator\DBAL\Types\Types}
 *
 * @link https://laravel.com/docs/master/migrations#available-column-types
 * @method static self BIG_INTEGER()
 * @method static self BIG_INCREMENTS()
 * @method static self BINARY()
 * @method static self BOOLEAN()
 * @method static self CHAR()
 * @method static self DATE()
 * @method static self DATETIME()
 * @method static self DATETIME_TZ()
 * @method static self DECIMAL()
 * @method static self DOUBLE()
 * @method static self ENUM()
 * @method static self FLOAT()
 * @method static self GEOMETRY()
 * @method static self GEOMETRY_COLLECTION()
 * @method static self INCREMENTS()
 * @method static self INTEGER()
 * @method static self IP_ADDRESS()
 * @method static self JSON()
 * @method static self JSONB()
 * @method static self LINE_STRING()
 * @method static self LONG_TEXT()
 * @method static self MAC_ADDRESS()
 * @method static self MEDIUM_INCREMENTS()
 * @method static self MEDIUM_INTEGER()
 * @method static self MEDIUM_TEXT()
 * @method static self MULTI_LINE_STRING()
 * @method static self MULTI_POINT()
 * @method static self MULTI_POLYGON()
 * @method static self POINT()
 * @method static self POLYGON()
 * @method static self REMEMBER_TOKEN()
 * @method static self SET()
 * @method static self SMALL_INCREMENTS()
 * @method static self SMALL_INTEGER()
 * @method static self SOFT_DELETES()
 * @method static self SOFT_DELETES_TZ()
 * @method static self STRING()
 * @method static self TEXT()
 * @method static self TIME()
 * @method static self TIME_TZ()
 * @method static self TIMESTAMP()
 * @method static self TIMESTAMPS()
 * @method static self TIMESTAMP_TZ()
 * @method static self TIMESTAMPS_TZ()
 * @method static self TINY_INCREMENTS()
 * @method static self TINY_INTEGER()
 * @method static self UNSIGNED_BIG_INTEGER()
 * @method static self UNSIGNED_DECIMAL()
 * @method static self UNSIGNED_INTEGER()
 * @method static self UNSIGNED_MEDIUM_INTEGER()
 * @method static self UNSIGNED_SMALL_INTEGER()
 * @method static self UNSIGNED_TINY_INTEGER()
 * @method static self UUID()
 * @method static self YEAR()
 */
final class ColumnType extends Enum
{
    public const BIG_INTEGER             = 'bigInteger';
    public const BIG_INCREMENTS          = 'bigIncrements';
    public const BINARY                  = 'binary';
    public const BOOLEAN                 = 'boolean';
    public const CHAR                    = 'char';
    public const DATE                    = 'date';
    public const DATETIME                = 'dateTime';
    public const DATETIME_TZ             = 'dateTimeTz';
    public const DECIMAL                 = 'decimal';
    public const DOUBLE                  = 'double';
    public const ENUM                    = 'enum';
    public const FLOAT                   = 'float';
    public const GEOMETRY                = 'geometry';
    public const GEOMETRY_COLLECTION     = 'geometryCollection';
    public const INCREMENTS              = 'increments';
    public const INTEGER                 = 'integer';
    public const IP_ADDRESS              = 'ipAddress';
    public const JSON                    = 'json';
    public const JSONB                   = 'jsonb';
    public const LINE_STRING             = 'lineString';
    public const LONG_TEXT               = 'longText';
    public const MAC_ADDRESS             = 'macAddress';
    public const MEDIUM_INCREMENTS       = 'mediumIncrements';
    public const MEDIUM_INTEGER          = 'mediumInteger';
    public const MEDIUM_TEXT             = 'mediumText';
    public const MULTI_LINE_STRING       = 'multiLineString';
    public const MULTI_POINT             = 'multiPoint';
    public const MULTI_POLYGON           = 'multiPolygon';
    public const POINT                   = 'point';
    public const POLYGON                 = 'polygon';
    public const REMEMBER_TOKEN          = 'rememberToken';
    public const SET                     = 'set';
    public const SMALL_INCREMENTS        = 'smallIncrements';
    public const SMALL_INTEGER           = 'smallInteger';
    public const SOFT_DELETES            = 'softDeletes';
    public const SOFT_DELETES_TZ         = 'softDeletesTz';
    public const STRING                  = 'string';
    public const TEXT                    = 'text';
    public const TIME                    = 'time';
    public const TIME_TZ                 = 'timeTz';
    public const TIMESTAMP               = 'timestamp';
    public const TIMESTAMPS              = 'timestamps';
    public const TIMESTAMP_TZ            = 'timestampTz';
    public const TIMESTAMPS_TZ           = 'timestampsTz';
    public const TINY_INCREMENTS         = 'tinyIncrements';
    public const TINY_INTEGER            = 'tinyInteger';
    public const UNSIGNED_BIG_INTEGER    = 'unsignedBigInteger';
    public const UNSIGNED_DECIMAL        = 'unsignedDecimal';
    public const UNSIGNED_INTEGER        = 'unsignedInteger';
    public const UNSIGNED_MEDIUM_INTEGER = 'unsignedMediumInteger';
    public const UNSIGNED_SMALL_INTEGER  = 'unsignedSmallInteger';
    public const UNSIGNED_TINY_INTEGER   = 'unsignedTinyInteger';
    public const UUID                    = 'uuid';
    public const YEAR                    = 'year';

    /**
     * Create instance from {@see \Doctrine\DBAL\Types\Type}.
     *
     * @param  \Doctrine\DBAL\Types\Type  $dbalType
     * @return static
     */
    public static function fromDBALType(Type $dbalType): self
    {
        $map = Types::BUILTIN_TYPES_MAP + Types::ADDITIONAL_TYPES_MAP;
        return self::from($map[get_class($dbalType)]);
    }
}
