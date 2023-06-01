<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

use MyCLabs\Enum\Enum;

/**
 * Preserved foreign key methods of the framework.
 *
 * @see https://laravel.com/docs/master/migrations#foreign-key-constraints
 * @method static self DROP_FOREIGN()
 * @method static self FOREIGN()
 * @method static self ON()
 * @method static self ON_DELETE()
 * @method static self ON_UPDATE()
 * @method static self REFERENCES()
 * @extends \MyCLabs\Enum\Enum<string>
 */
class Foreign extends Enum
{
    public const DROP_FOREIGN = 'dropForeign';
    public const FOREIGN      = 'foreign';
    public const ON           = 'on';
    public const ON_DELETE    = 'onDelete';
    public const ON_UPDATE    = 'onUpdate';
    public const REFERENCES   = 'references';
}
