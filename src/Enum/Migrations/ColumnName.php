<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations;

/**
 * Preserved column names used by the framework.
 *
 * @see https://laravel.com/docs/master/migrations#available-column-types
 */
enum ColumnName: string
{
    case CREATED_AT     = 'created_at';
    case DELETED_AT     = 'deleted_at';
    case REMEMBER_TOKEN = 'remember_token';
    case UPDATED_AT     = 'updated_at';
}
