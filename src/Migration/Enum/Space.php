<?php

namespace KitLoong\MigrationsGenerator\Migration\Enum;

enum Space: string
{
    case LINE_BREAK = "\n";
    case TAB        = '    '; // 4 spaces tab
}
