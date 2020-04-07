<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/07
 */

namespace KitLoong\MigrationsGenerator\MigrationMethod;

class PgSQLGeography
{
    const MAP = [
        'geography(geometry,4326)' => ColumnType::GEOMETRY,
        'geography(geometrycollection,4326)' => ColumnType::GEOMETRY_COLLECTION,
        'geography(linestring,4326)' => ColumnType::LINE_STRING,
        'geography(multilinestring,4326)' => ColumnType::MULTI_LINE_STRING,
        'geography(multipoint,4326)' => ColumnType::MULTI_POINT,
        'geography(multipolygon,4326)' => ColumnType::MULTI_POLYGON,
        'geography(point,4326)' => ColumnType::POINT,
        'geography(polygon,4326)' => ColumnType::POLYGON
    ];
}
