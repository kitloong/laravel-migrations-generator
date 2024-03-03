<?php

namespace KitLoong\MigrationsGenerator\Support;

use Illuminate\Database\Schema\Blueprint;

trait CheckMigrationMethod
{
    /**
     * `geography` added since Laravel 11.
     */
    public function hasGeography(): bool
    {
        return method_exists(Blueprint::class, 'geography');
    }
}
