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
 * @extends \MyCLabs\Enum\Enum<string>
 */
final class TableProperty extends Enum
{
    public const CHARSET   = 'charset';
    public const COLLATION = 'collation';
    public const ENGINE    = 'engine';
}
