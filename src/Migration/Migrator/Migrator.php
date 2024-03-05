<?php

namespace KitLoong\MigrationsGenerator\Migration\Migrator;

use Illuminate\Database\Migrations\Migrator as DefaultMigrator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Migrator extends DefaultMigrator
{
    /**
     * Scan and get vendors' loaded migration table names.
     *
     * @return string[]
     */
    public function getVendorTableNames(): array
    {
        $tables = [];

        // Backup the current DB connection.
        $previousConnection = DB::getDefaultConnection();

        try {
            // Create an in-memory SQLite database for the migrations generator.
            // Note that no real migrations will be executed, this is simply a precautionary switch to in-memory SQLite.
            Config::set('database.connections.lgm_sqlite', [
                'driver'   => 'sqlite',
                'database' => ':memory:',
            ]);

            DB::setDefaultConnection('lgm_sqlite');

            $vendorPaths = app('migrator')->paths();

            foreach ($vendorPaths as $path) {
                $files = File::files($path);

                foreach ($files as $file) {
                    $queries = $this->getMigrationQueries($file->getPathname());

                    foreach ($queries as $q) {
                        $matched = Str::match('/^create table ["|`](.*?)["|`]/', $q['query']);

                        if ($matched === '') {
                            continue;
                        }

                        $tables[] = $matched;
                    }
                }
            }
        } finally {
            // Restore backup DB connection.
            DB::setDefaultConnection($previousConnection);
        }

        return $tables;
    }

    /**
     * Resolve migration instance from `$path` and get all of the queries that would be run for a migration.
     *
     * @return array<int, array{'query': string, 'bindings': array<string, array<mixed>>, 'time': float|null}>
     */
    protected function getMigrationQueries(string $path): array
    {
        $migration = $this->resolveMigration($path);

        return $this->getQueries($migration, 'up');
    }

    /**
     * Resolve migration instance with backward compatibility.
     */
    protected function resolveMigration(string $path): object
    {
        return $this->resolvePath($path);
    }
}
