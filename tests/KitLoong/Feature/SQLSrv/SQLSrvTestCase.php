<?php

namespace Tests\KitLoong\Feature\SQLSrv;

use Illuminate\Support\Facades\Schema;
use Tests\KitLoong\Feature\FeatureTestCase;

abstract class SQLSrvTestCase extends FeatureTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.default', 'sqlsrv');
        $app['config']->set('database.connections.sqlsrv', [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('SQLSRV_HOST'),
            'port' => env('SQLSRV_PORT'),
            'database' => env('SQLSRV_DATABASE'),
            'username' => env('SQLSRV_USERNAME'),
            'password' => env('SQLSRV_PASSWORD'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ]);
    }

    protected function dumpSchemaAs(string $destination): void
    {
        $tables = Schema::connection('sqlsrv')->getConnection()->getDoctrineSchemaManager()->listTableNames();
        $sqls = [];
        foreach ($tables as $table) {
            $sqls[] = "EXEC sp_columns $table;";
        }

        $command = sprintf(
            'sqlcmd -S %s -U %s -P \'%s\' -d %s -Q "%s" -o "%s"',
            config('database.connections.sqlsrv.host'),
            config('database.connections.sqlsrv.username'),
            config('database.connections.sqlsrv.password'),
            config('database.connections.sqlsrv.database'),
            implode('', $sqls),
            $destination
        );
        exec($command);
    }

    protected function dropAllTables(): void
    {
        $tables = Schema::connection('sqlsrv')->getConnection()->getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            Schema::drop($table);
        }
    }
}
