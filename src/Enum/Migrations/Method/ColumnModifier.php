<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

/**
 * Preserved column modifier of the framework.
 *
 * @see https://laravel.com/docs/master/migrations#column-modifiers
 */
enum ColumnModifier: string implements MethodName
{
    case ALWAYS                = 'always';
    case AUTO_INCREMENT        = 'autoIncrement';
    case CHARSET               = 'charset';
    case COLLATION             = 'collation';
    case COMMENT               = 'comment';
    case DEFAULT               = 'default';
    case GENERATED_AS          = 'generatedAs';
    case NULLABLE              = 'nullable';
    case STORED_AS             = 'storedAs';
    case UNSIGNED              = 'unsigned';
    case USE_CURRENT           = 'useCurrent';
    case USE_CURRENT_ON_UPDATE = 'useCurrentOnUpdate';
    case VIRTUAL_AS            = 'virtualAs';
}
