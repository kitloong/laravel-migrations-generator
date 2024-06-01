<?php

namespace KitLoong\MigrationsGenerator\Enum;

/**
 * Framework DB connection driver name.
 */
enum Driver: string
{
    case MARIADB = 'mariadb';
    case MYSQL   = 'mysql';
    case PGSQL   = 'pgsql';
    case SQLITE  = 'sqlite';
    case SQLSRV  = 'sqlsrv';
}
