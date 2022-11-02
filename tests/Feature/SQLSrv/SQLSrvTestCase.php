<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\SQLSrv;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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

    protected function setUp(): void
    {
        parent::setUp();

        // Drop first.
        DB::statement("DROP TYPE IF EXISTS accountnumber");

        // Create for custom column type test.
        DB::statement("CREATE TYPE accountnumber FROM [nvarchar](15) NULL");
    }

    protected function dumpSchemaAs(string $destination): void
    {
        $tables = DB::getDoctrineSchemaManager()->listTableNames();
        $sqls   = [];

        foreach ($tables as $table) {
            $sqls[] = "EXEC sp_help '" . $table . "';";
        }

        $views = DB::getDoctrineSchemaManager()->listViews();

        foreach ($views as $view) {
            $sqls[] = "EXEC sp_helptext '" . $view->getName() . "';";
        }

        $procedures = $this->getAllProcedures();

        foreach ($procedures as $procedure) {
            $sqls[] = "EXEC sp_helptext '" . $procedure->name . "';";
        }

        $command = sprintf(
            'sqlcmd -S tcp:%s,%s -U %s -P \'%s\' -d %s -Q "%s" -o "%s"',
            config('database.connections.sqlsrv.host'),
            config('database.connections.sqlsrv.port'),
            config('database.connections.sqlsrv.username'),
            config('database.connections.sqlsrv.password'),
            config('database.connections.sqlsrv.database'),
            implode('', $sqls),
            $this->getStorageSqlPath('temp.sql')
        );
        exec($command);

        $this->removeDynamicInformation($this->getStorageSqlPath('temp.sql'), $destination);
    }

    protected function refreshDatabase(): void
    {
        $this->dropAllViews();
        Schema::dropAllTables();
        $this->dropAllProcedures();
    }

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
        $procedures = $this->getAllProcedures();

        foreach ($procedures as $procedure) {
            DB::unprepared("DROP PROCEDURE IF EXISTS " . $procedure->name);
        }
    }

    protected function getAllProcedures(): array
    {
        return DB::select(
            "SELECT name, definition
            FROM sys.sysobjects
                INNER JOIN sys.sql_modules ON (sys.sysobjects.id = sys.sql_modules.object_id)
            WHERE type = 'P'
                AND definition IS NOT NULL
            ORDER BY name"
        );
    }

    /**
     * Remove dynamic information from the SQL file.
     *
     * @param  string  $from  SQL source file path.
     * @param  string  $destination  Output path.
     * @return void
     */
    private function removeDynamicInformation(string $from, string $destination): void
    {
        $fromResource        = fopen($from, 'r');
        $destinationResource = fopen($destination, 'w+');

        if ($fromResource && $destinationResource) {
            while (($line = fgets($fromResource)) !== false) {
                $replaced = preg_replace('/^(.*)(user table)(.*)$/', '$1$2', $line);
                $replaced = preg_replace('/^(.*)(PK__.*__\w{16})(.*)$/', '$1PK__replaced__xxxxxxxxxxxxxxxx$3', $replaced);
                $replaced = preg_replace('/^(.*)(DF__.*__\w{8})(.*)$/', '$1DF__replacedreplaced__xxxxxxxx$3', $replaced);
                $replaced = preg_replace('/^(.*)(CK__.*__\w{8})(.*)$/', '$1CK__replacedreplaced__xxxxxxxx$3', $replaced);
                fwrite($destinationResource, $replaced);
            }

            fclose($fromResource);
            fclose($destinationResource);
        }

        File::delete($from);
    }
}
