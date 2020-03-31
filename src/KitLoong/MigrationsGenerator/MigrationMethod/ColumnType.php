<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/30
 * Time: 21:39
 */

namespace KitLoong\MigrationsGenerator\MigrationMethod;

final class ColumnType
{
    const BIG_INTEGER = 'bigInteger';
    const BINARY = 'binary';
    const BOOLEAN = 'boolean';
    const CHAR = 'char';
    const DATETIME = 'dateTime';
    const DOUBLE = 'double';
    const ENUM = 'enum';
    const GEOMETRY = 'geometry';
    const GEOMETRY_COLLECTION = 'geometryCollection';
    const INCREMENTS = 'increments';
    const IP_ADDRESS = 'ipAddress';
    const JSON = 'json';
    const JSONB = 'jsonb';
    const MEDIUM_INTEGER = 'mediumInteger';
    const POINT = 'point';
    const REMEMBER_TOKEN = 'rememberToken';
    const SET = 'set';
    const SMALL_INTEGER = 'smallInteger';
    const SOFT_DELETES = 'softDeletes';
    const TIMESTAMP = 'timestamp';
    const TIMESTAMPS = 'timestamps';
    const TINY_INTEGER = 'tinyInteger';
    const UUID = 'uuid';
}
