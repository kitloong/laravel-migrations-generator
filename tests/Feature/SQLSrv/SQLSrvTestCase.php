<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\SQLSrv;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use KitLoong\MigrationsGenerator\Support\CheckLaravelVersion;
use KitLoong\MigrationsGenerator\Tests\Feature\FeatureTestCase;

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

    protected function dumpSchemaAs(string $destination): void
    {
        $tables = DB::getDoctrineSchemaManager()->listTableNames();
        $sqls   = [];
        foreach ($tables as $table) {
            $sqls[] = "EXEC sp_columns '" . $table . "';";
        }

        $views = DB::getDoctrineSchemaManager()->listViews();
        foreach ($views as $view) {
            $sqls[] = "EXEC sp_helptext '" . $view->getName() . "';";
        }

        $command = sprintf(
            'sqlcmd -S tcp:%s,%s -U %s -P \'%s\' -d %s -Q "%s" -o "%s"',
            config('database.connections.sqlsrv.host'),
            config('database.connections.sqlsrv.port'),
            config('database.connections.sqlsrv.username'),
            config('database.connections.sqlsrv.password'),
            config('database.connections.sqlsrv.database'),
            implode('', $sqls),
            $destination
        );
        exec($command);
    }

    protected function refreshDatabase(): void
    {
        $this->dropAllViews();
        Schema::dropAllTables();
        $this->dropAllProcedures();
    }

    /**
     * @return void
     */
    protected function dropAllViews(): void
    {
        // `dropAllViews` available in Laravel >= 6.x
        if ($this->atLeastLaravel6()) {
            Schema::dropAllViews();
            return;
        }

        // See https://github.com/laravel/framework/blob/6.x/src/Illuminate/Database/Schema/Grammars/SqlServerGrammar.php#L360
        DB::statement(
            "DECLARE @sql NVARCHAR(MAX) = N'';
            SELECT @sql += 'DROP VIEW ' + QUOTENAME(OBJECT_SCHEMA_NAME(object_id)) + '.' + QUOTENAME(name) + ';'
            FROM sys.views;

            EXEC sp_executesql @sql;"
        );
    }

    protected function dropAllProcedures(): void
    {
        $procedures = DB::select(
            "SELECT name, definition
            FROM sys.sysobjects
                INNER JOIN sys.sql_modules ON (sys.sysobjects.id = sys.sql_modules.object_id)
            WHERE type = 'P'
                AND definition IS NOT NULL
            ORDER BY name"
        );

        foreach ($procedures as $procedure) {
            DB::statement("DROP PROCEDURE IF EXISTS " . $procedure->name);
        }
    }
}
