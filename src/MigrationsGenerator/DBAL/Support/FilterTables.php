<?php

namespace MigrationsGenerator\DBAL\Support;

use Closure;
use Illuminate\Support\Facades\DB;
use MigrationsGenerator\DBAL\Platform;
use MigrationsGenerator\MigrationsGeneratorSetting;

trait FilterTables
{
    /**
     * Filter tables.
     * For PgSQL, we get only tables from user defined `schema`.
     *
     * @return \Closure
     */
    public function filterTableNameCallback(): Closure
    {
        return function (string $table) {
            if (app(MigrationsGeneratorSetting::class)->getPlatform() === Platform::POSTGRESQL) {
                return $this->isPgSQLWantedTable($table);
            }

            return true;
        };
    }

    /**
     * Checks if table is from user defined `schema`.
     *
     * @param  string  $table
     * @return bool
     */
    protected function isPgSQLWantedTable(string $table): bool
    {
        // If table name do not have namespace, it is using the default namespace.
        if (strpos($table, '.') === false) {
            return true;
        }

        // Schema name defined in Laravel framework.
        $schema = app(MigrationsGeneratorSetting::class)->getConnection()->getConfig('schema');

        $parts     = explode('.', $table);
        $namespace = $parts[0];

        return $namespace === $schema;
    }
}
