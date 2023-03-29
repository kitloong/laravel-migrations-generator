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
    private const ALWAYS                = 'always';
    private const AUTO_INCREMENT        = 'autoIncrement';
    private const CHARSET               = 'charset';
    private const COLLATION             = 'collation';
    private const COMMENT               = 'comment';
    private const DEFAULT               = 'default';
    private const GENERATED_AS          = 'generatedAs';
    private const NULLABLE              = 'nullable';
    private const STORED_AS             = 'storedAs';
    private const UNSIGNED              = 'unsigned';
    private const USE_CURRENT           = 'useCurrent';
    private const USE_CURRENT_ON_UPDATE = 'useCurrentOnUpdate';
    private const VIRTUAL_AS            = 'virtualAs';
}
