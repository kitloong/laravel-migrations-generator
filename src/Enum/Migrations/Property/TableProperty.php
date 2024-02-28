<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Property;

/**
 * Preserved table properties of the framework.
 *
 * @see https://laravel.com/docs/master/migrations#tables
 */
enum TableProperty: string implements PropertyName
{
    case CHARSET   = 'charset';
    case COLLATION = 'collation';
    case ENGINE    = 'engine';
}
