<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

use MyCLabs\Enum\Enum;

/**
 * Preserved table methods of the framework.
 *
 * @see https://laravel.com/docs/master/migrations#tables
 * @method static self COMMENT()
 * @extends \MyCLabs\Enum\Enum<string>
 */
final class TableMethod extends Enum
{
    private const COMMENT = 'comment';
}
