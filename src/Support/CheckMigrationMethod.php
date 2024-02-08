<?php

namespace KitLoong\MigrationsGenerator\Support;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Schema\Blueprint;

trait CheckMigrationMethod
{
    /**
     * `tinyText` added since Laravel 9.
     */
    public function hasULID(): bool
    {
        return method_exists(Blueprint::class, 'ulid');
    }

    /**
     * Check if support anonymous migration.
     * This feature is added in late Laravel v8 and above.
     */
    public function hasAnonymousMigration(): bool
    {
        return method_exists(Migrator::class, 'getMigrationClass');
    }

    /**
     * Check if support add comment to a table.
     * This feature is added since Laravel v9.
     */
    public function hasTableComment(): bool
    {
        return method_exists(Blueprint::class, 'comment');
    }

    /**
     * `geography` added since Laravel 11.
     */
    public function hasGeography(): bool
    {
        return method_exists(Blueprint::class, 'geography');
    }
}
