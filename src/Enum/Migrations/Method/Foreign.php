<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

/**
 * Preserved foreign key methods of the framework.
 *
 * @see https://laravel.com/docs/master/migrations#foreign-key-constraints
 */
enum Foreign: string implements MethodName
{
    case DROP_FOREIGN = 'dropForeign';
    case FOREIGN      = 'foreign';
    case ON           = 'on';
    case ON_DELETE    = 'onDelete';
    case ON_UPDATE    = 'onUpdate';
    case REFERENCES   = 'references';
}
