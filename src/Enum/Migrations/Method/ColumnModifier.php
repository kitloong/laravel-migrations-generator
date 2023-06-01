<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

use MyCLabs\Enum\Enum;

/**
 * Preserved column modifier of the framework.
 *
 * @see https://laravel.com/docs/master/migrations#column-modifiers
 * @method static self ALWAYS()
 * @method static self AUTO_INCREMENT()
 * @method static self CHARSET()
 * @method static self COLLATION()
 * @method static self COMMENT()
 * @method static self DEFAULT()
 * @method static self GENERATED_AS()
 * @method static self NULLABLE()
 * @method static self STORED_AS()
 * @method static self UNSIGNED()
 * @method static self USE_CURRENT()
 * @method static self USE_CURRENT_ON_UPDATE()
 * @method static self VIRTUAL_AS()
 * @extends \MyCLabs\Enum\Enum<string>
 */
final class ColumnModifier extends Enum
{
    public const ALWAYS                = 'always';
    public const AUTO_INCREMENT        = 'autoIncrement';
    public const CHARSET               = 'charset';
    public const COLLATION             = 'collation';
    public const COMMENT               = 'comment';
    public const DEFAULT               = 'default';
    public const GENERATED_AS          = 'generatedAs';
    public const NULLABLE              = 'nullable';
    public const STORED_AS             = 'storedAs';
    public const UNSIGNED              = 'unsigned';
    public const USE_CURRENT           = 'useCurrent';
    public const USE_CURRENT_ON_UPDATE = 'useCurrentOnUpdate';
    public const VIRTUAL_AS            = 'virtualAs';
}
