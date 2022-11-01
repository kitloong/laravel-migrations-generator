<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Property;

use MyCLabs\Enum\Enum;

/**
 * Preserved table properties of the framework.
 *
 * @see https://laravel.com/docs/master/migrations#tables
 * @method static self CHARSET()
 * @method static self COLLATION()
 * @method static self ENGINE()
 */
final class TableProperty extends Enum
{
    private const CHARSET   = 'charset';
    private const COLLATION = 'collation';
    private const ENGINE    = 'engine';
}
