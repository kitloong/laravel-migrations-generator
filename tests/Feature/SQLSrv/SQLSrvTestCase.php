<?php

namespace Tests\Feature\SQLSrv;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MigrationsGenerator\Support\CheckLaravelVersion;
use Tests\Feature\FeatureTestCase;

abstract class SQLSrvTestCase extends FeatureTestCase
{
    use CheckLaravelVersion;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.default', 'sqlsrv');
        $app['config']->set('database.connections.sqlsrv', [
            'driver'         => 'sqlsrv',
            'url'            => env('DATABASE_URL'),
            'host'           => env('SQLSRV_HOST'),
            'port'           => env('SQLSRV_PORT'),
            'database'       => env('SQLSRV_DATABASE'),
            'username'       => env('SQLSRV_USERNAME'),
            'password'       => env('SQLSRV_PASSWORD'),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
        ]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function dumpSchemaAs(string $destination): void
    {
        $tables = Schema::connection('sqlsrv')->getConnection()->getDoctrineSchemaManager()->listTableNames();
        $sqls   = [];
        foreach ($tables as $table) {
            $sqls[] = "EXEC sp_columns '".$table."';";
        }

        $views = Schema::connection('sqlsrv')->getConnection()->getDoctrineSchemaManager()->listViews();
        foreach ($views as $view) {
            $sqls[] = "EXEC sp_helptext '".$view->getName()."';";
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
        // Method `typeDateTime` is using different implementation since v5.8
        // It is hard to create unified UT across Laravel <= v5.7 and >= v5.8
        // To simplify, dropping UT check for version <= 5.7.
        // https://github.com/laravel/framework/blob/5.7/src/Illuminate/Database/Schema/Grammars/SqlServerGrammar.php#L523
        // https://github.com/laravel/framework/blob/5.8/src/Illuminate/Database/Schema/Grammars/SqlServerGrammar.php#L538
        if (!$this->atLeastLaravel5Dot8()) {
            $this->markTestSkipped();
        }

        // `dropAllViews` available in Laravel >= 6.x
        if ($this->atLeastLaravel6()) {
            Schema::connection('sqlsrv')->dropAllViews();
        } else {
            // See https://github.com/laravel/framework/blob/6.x/src/Illuminate/Database/Schema/Grammars/SqlServerGrammar.php#L360
            DB::statement("DECLARE @sql NVARCHAR(MAX) = N'';
            SELECT @sql += 'DROP VIEW ' + QUOTENAME(OBJECT_SCHEMA_NAME(object_id)) + '.' + QUOTENAME(name) + ';'
            FROM sys.views;

            EXEC sp_executesql @sql;");
        }
        Schema::connection('sqlsrv')->dropAllTables();
    }
}
