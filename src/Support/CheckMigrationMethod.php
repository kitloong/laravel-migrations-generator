<?php

namespace KitLoong\MigrationsGenerator\Support;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;

trait CheckMigrationMethod
{
    use CheckLaravelVersion;

    /**
     * `useCurrentOnUpdate` added since Laravel 8.
     *
     * @return bool
     */
    public function hasUseCurrentOnUpdate(): bool
    {
        return $this->atLeastLaravel8();
    }

    /**
     * `set` added since Laravel 5.8.
     *
     * @return bool
     */
    public function hasSet(): bool
    {
        return method_exists(Blueprint::class, 'set');
    }

    /**
     * `fulltext` added since Laravel 8.
     *
     * @return bool
     */
    public function hasFullText(): bool
    {
        return method_exists(Grammar::class, 'compileFulltext');
    }

    /**
     * Check if support anonymous migration.
     * This feature is added in late Laravel v8 and above.
     *
     * @return bool
     */
    public function hasAnonymousMigration(): bool
    {
        return method_exists(Migrator::class, 'getMigrationClass');
    }

    /**
     * Check if support add comment to a table.
     * This feature is added since Laravel v9.
     *
     * @return bool
     */
    public function hasTableComment(): bool
    {
        return method_exists(Blueprint::class, 'comment');
    }
}
