<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations;

use MyCLabs\Enum\Enum;

/**
 * Preserved column names used by the framework.
 *
 * @see https://laravel.com/docs/master/migrations#available-column-types
 * @method static self CREATED_AT()
 * @method static self DELETED_AT()
 * @method static self REMEMBER_TOKEN()
 * @method static self UPDATED_AT()
 * @extends \MyCLabs\Enum\Enum<string>
 */
final class ColumnName extends Enum
{
    private const CREATED_AT     = 'created_at';
    private const DELETED_AT     = 'deleted_at';
    private const REMEMBER_TOKEN = 'remember_token';
    private const UPDATED_AT     = 'updated_at';
}
