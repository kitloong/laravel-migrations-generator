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
    public const MYSQL  = 'mysql';
    public const PGSQL  = 'pgsql';
    public const SQLITE = 'sqlite';
    public const SQLSRV = 'sqlsrv';
}
