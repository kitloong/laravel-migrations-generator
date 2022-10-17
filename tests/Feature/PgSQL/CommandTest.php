<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\PgSQL;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use KitLoong\MigrationsGenerator\Support\CheckLaravelVersion;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CommandTest extends PgSQLTestCase
{
    use CheckLaravelVersion;

    public function testRun()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('pgsql');

            // Test timestamp default now()
            DB::statement(
                "ALTER TABLE all_columns_pgsql ADD COLUMN timestamp_defaultnow timestamp(0) without time zone DEFAULT now() NOT NULL"
            );

            DB::statement(
                "ALTER TABLE all_columns_pgsql ADD COLUMN status my_status NOT NULL"
            );

            DB::statement(
                "ALTER TABLE all_columns_pgsql ADD COLUMN timestamp_default_timezone_now timestamp(0) without time zone DEFAULT timezone('Europe/Rome'::text, now()) NOT NULL"
            );
        };

        $generateMigrations = function () {
            $this->generateMigrations();
        };

        $beforeVerify = function () {
            $this->assertLineExistsThenReplace(
                $this->getStorageSqlPath('actual.sql'),
                'timestamp_defaultnow timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL'
            );

            $this->assertLineExistsThenReplace(
                $this->getStorageSqlPath('expected.sql'),
                'timestamp_defaultnow timestamp(0) without time zone DEFAULT now() NOT NULL'
            );
        };

        $this->verify($migrateTemplates, $generateMigrations, $beforeVerify);
    }

    public function testSquashUp()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('pgsql');

            DB::statement(
                "ALTER TABLE all_columns_pgsql ADD COLUMN status my_status NOT NULL"
            );
        };

        $generateMigrations = function () {
            $this->generateMigrations(['--squash' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testCollation()
    {
        $migrateTemplates = function () {
            $this->migrateCollation('pgsql');
        };

        $generateMigrations = function () {
            $this->generateMigrations(['--use-db-collation' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testIgnore()
    {
        $this->migrateGeneral('pgsql');

        $this->truncateMigrationsTable();

        $this->generateMigrations([
            '--ignore' => implode(',', [
                'name-with-hyphen-pgsql',
                'name-with-hyphen-pgsql_view',
            ]),
        ]);

        $this->refreshDatabase();

        $this->runMigrationsFrom('pgsql', $this->getStorageMigrationsPath());

        $tables = $this->getTableNames();
        $views  = $this->getViewNames();

        $this->assertNotContains('name-with-hyphen-pgsql', $tables);
        $this->assertNotContains('public."name-with-hyphen-pgsql_view"', $views);
    }

    /**
     * Start from Laravel 9, the `schema` configuration option used to configure Postgres connection search paths renamed to `search_path`.
     *
     * @see https://laravel.com/docs/9.x/upgrade#postgres-schema-configuration
     * @return void
     */
    public function testRunWithSearchPath()
    {
        if (!$this->atLeastLaravel9()) {
            $this->markTestSkipped();
        }

        // Unset `schema`
        Config::set('database.connections.pgsql.schema');
        $this->assertNull(Config::get('database.connections.pgsql.schema'));

        // Use `search_path`
        Config::set('database.connections.pgsql.search_path', 'public');

        $migrateTemplates = function () {
            $this->migrateGeneral('pgsql');
        };

        $generateMigrations = function () {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    private function verify(callable $migrateTemplates, callable $generateMigrations, ?callable $beforeVerify = null)
    {
        $migrateTemplates();

        $this->truncateMigrationsTable();
        $this->dumpSchemaAs($this->getStorageSqlPath('expected.sql'));

        $generateMigrations();

        $this->assertMigrations();

        $this->refreshDatabase();

        $this->runMigrationsFrom('pgsql', $this->getStorageMigrationsPath());

        $this->truncateMigrationsTable();
        $this->dumpSchemaAs($this->getStorageSqlPath('actual.sql'));

        $beforeVerify === null ?: $beforeVerify();

        $this->assertFileEqualsIgnoringOrder(
            $this->getStorageSqlPath('expected.sql'),
            $this->getStorageSqlPath('actual.sql')
        );
    }

    private function assertLineExistsThenReplace(string $file, string $line)
    {
        $this->assertTrue(
            str_contains(
                file_get_contents($file),
                $line
            )
        );

        File::put(
            $file,
            str_replace(
                $line,
                'replaced',
                file_get_contents($file)
            )
        );
    }
}
