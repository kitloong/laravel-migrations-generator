<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

/**
 * Preserved table methods of the framework.
 *
 * @see https://laravel.com/docs/master/migrations#tables
 */
enum TableMethod: string implements MethodName
{
    case COMMENT = 'comment';
}
