<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

use MyCLabs\Enum\Enum;

/**
 * Preserved method names for migration files by the framework.
 *
 * @see https://laravel.com/docs/master/migrations#tables
 * @method static self CONNECTION()
 * @method static self CREATE()
 * @method static self DROP_IF_EXISTS()
 * @method static self HAS_TABLE()
 * @method static self TABLE()
 */
final class SchemaBuilder extends Enum
{
    private const CONNECTION     = 'connection';
    private const CREATE         = 'create';
    private const DROP_IF_EXISTS = 'dropIfExists';
    private const HAS_TABLE      = 'hasTable';
    private const TABLE          = 'table';
}
