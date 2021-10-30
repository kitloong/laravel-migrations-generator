<?php

namespace Tests\Feature\PgSQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTestCase;

abstract class PgSQLTestCase extends FeatureTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.default', 'pgsql');
        $app['config']->set('database.connections.pgsql', [
            'driver'         => 'pgsql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('POSTGRES_HOST'),
            'port'           => env('POSTGRES_PORT'),
            'database'       => env('POSTGRES_DATABASE'),
            'username'       => env('POSTGRES_USERNAME'),
            'password'       => env('POSTGRES_PASSWORD'),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'schema'         => 'public',
            'sslmode'        => 'prefer',
        ]);
    }

    protected function dumpSchemaAs(string $destination): void
    {
        $command = sprintf(
            'PGPASSWORD="%s" pg_dump -h %s -U %s %s -f %s --schema-only',
            config('database.connections.pgsql.password'),
            config('database.connections.pgsql.host'),
            config('database.connections.pgsql.username'),
            config('database.connections.pgsql.database'),
            $destination
        );
        exec($command);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function dropAllTables(): void
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            if (Str::startsWith($table, 'tiger.')) {
                continue;
            }

            if (Str::startsWith($table, 'topology.')) {
                continue;
            }

            // CASCADE, Automatically drop objects that depend on the table (such as views).
            DB::statement("DROP TABLE if exists $table cascade");
        }
    }
}
