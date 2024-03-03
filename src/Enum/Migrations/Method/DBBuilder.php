<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

enum DBBuilder: string implements MethodName
{
    case STATEMENT  = 'statement';
    case UNPREPARED = 'unprepared';
}
