<?php

namespace MigrationsGenerator\DBAL\Support;

use Closure;
use Doctrine\DBAL\Schema\Table;
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
    public function filterTableCallback(): Closure
    {
        return function (Table $table) {
            if (app(MigrationsGeneratorSetting::class)->getPlatform() === Platform::POSTGRESQL) {
                return $this->isPgSQLWantedTable($table);
            }

            return true;
        };
    }

    /**
     * Checks if table is from user defined `schema`.
     *
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @return bool
     */
    protected function isPgSQLWantedTable(Table $table): bool
    {
        $schema = DB::connection()->getConfig('schema');
        if ($table->isInDefaultNamespace($schema) || $table->getNamespaceName() === $schema) {
            return true;
        }
        return false;
    }
}
