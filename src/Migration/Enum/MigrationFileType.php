<?php

namespace KitLoong\MigrationsGenerator\Migration\Enum;

enum MigrationFileType: string
{
    case FOREIGN_KEY = 'foreign_key';
    case TABLE       = 'table';
    case VIEW        = 'view';
    case PROCEDURE   = 'procedure';
}
