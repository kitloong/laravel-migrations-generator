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
 */
class Foreign extends Enum
{
    private const DROP_FOREIGN = 'dropForeign';
    private const FOREIGN      = 'foreign';
    private const ON           = 'on';
    private const ON_DELETE    = 'onDelete';
    private const ON_UPDATE    = 'onUpdate';
    private const REFERENCES   = 'references';
}
