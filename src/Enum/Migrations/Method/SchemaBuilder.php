<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

/**
 * Preserved method names for migration files by the framework.
 *
 * @see https://laravel.com/docs/master/migrations#tables
 */
enum SchemaBuilder: string implements MethodName
{
    case CONNECTION     = 'connection';
    case CREATE         = 'create';
    case DROP_IF_EXISTS = 'dropIfExists';
    case HAS_TABLE      = 'hasTable';
    case TABLE          = 'table';
}
