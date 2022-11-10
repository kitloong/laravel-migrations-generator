<?php

namespace KitLoong\MigrationsGenerator\Migration\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static self FOREIGN_KEY()
 * @method static self TABLE()
 * @method static self VIEW()
 * @method static self PROCEDURE()
 */
final class MigrationFileType extends Enum
{
    private const FOREIGN_KEY = 'foreign_key';
    private const TABLE       = 'table';
    private const VIEW        = 'view';
    private const PROCEDURE   = 'procedure';
}
