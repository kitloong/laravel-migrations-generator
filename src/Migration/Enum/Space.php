<?php

namespace KitLoong\MigrationsGenerator\Migration\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static self LINE_BREAK()
 * @method static self TAB()
 */
final class Space extends Enum
{
    private const LINE_BREAK = PHP_EOL;
    private const TAB        = '    '; // 4 spaces tab
}
