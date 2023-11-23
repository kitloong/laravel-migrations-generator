<?php

namespace KitLoong\MigrationsGenerator\Enum;

use MyCLabs\Enum\Enum;

/**
 * Framework DB connection driver name.
 *
 * @method static self MYSQL()
 * @method static self PGSQL()
 * @method static self SQLITE()
 * @method static self SQLSRV()
 * @extends \MyCLabs\Enum\Enum<string>
 */
final class Driver extends Enum
{
    private const MYSQL  = 'mysql';
    private const PGSQL  = 'pgsql';
    private const SQLITE = 'sqlite';
    private const SQLSRV = 'sqlsrv';
}
