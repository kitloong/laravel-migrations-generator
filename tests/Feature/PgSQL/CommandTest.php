<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\PgSQL;

use Illuminate\Support\Collection;
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

    public function testRun(): void
    {
        $migrateTemplates = function (): void {
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

        $generateMigrations = function (): void {
            $this->generateMigrations();
        };

        $beforeVerify = function (): void {
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

    public function testSquashUp(): void
    {
        $migrateTemplates = function (): void {
            $this->migrateGeneral('pgsql');

            DB::statement(
                "ALTER TABLE all_columns_pgsql ADD COLUMN status my_status NOT NULL"
            );
        };

        $generateMigrations = function (): void {
            $this->generateMigrations(['--squash' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testCollation(): void
    {
        $migrateTemplates = function (): void {
            $this->migrateCollation('pgsql');
        };

        $generateMigrations = function (): void {
            $this->generateMigrations(['--use-db-collation' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testIgnore(): void
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
     */
    public function testRunWithSearchPath(): void
    {
        if (!$this->atLeastLaravel9()) {
            $this->markTestSkipped();
        }

        // Unset `schema`
        Config::set('database.connections.pgsql.schema');
        $this->assertNull(Config::get('database.connections.pgsql.schema'));

        // Use `search_path`
        Config::set('database.connections.pgsql.search_path', 'public');

        $migrateTemplates = function (): void {
            $this->migrateGeneral('pgsql');
        };

        $generateMigrations = function (): void {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testWithHasTable(): void
    {
        $migrateTemplates = function (): void {
            $this->migrateGeneral('pgsql');

            DB::statement(
                "ALTER TABLE all_columns_pgsql ADD COLUMN status my_status NOT NULL"
            );
        };

        $generateMigrations = function (): void {
            $this->generateMigrations(['--with-has-table' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testWithHasTableSquash(): void
    {
        $migrateTemplates = function (): void {
            $this->migrateGeneral('pgsql');

            DB::statement(
                "ALTER TABLE all_columns_pgsql ADD COLUMN status my_status NOT NULL"
            );
        };

        $generateMigrations = function (): void {
            $this->generateMigrations(['--with-has-table' => true, '--squash' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testSkipVendor(): void
    {
        $this->migrateGeneral('pgsql');

        $this->migrateVendors('pgsql');

        // Load migrations from vendors path to mock vendors migration.
        // Loaded migrations should not be generated.
        app('migrator')->path($this->getStorageFromVendorsPath());

        $tables = $this->getTableNames();

        $vendors = [
            'personal_access_tokens_pgsql',
            'telescope_entries_pgsql',
            'telescope_entries_tags_pgsql',
            'telescope_monitoring_pgsql',
        ];

        foreach ($vendors as $vendor) {
            $this->assertContains($vendor, $tables);
        }

        $tablesWithoutVendors = (new Collection($tables))->filter(function ($table) use ($vendors) {
            return !in_array($table, $vendors);
        })
            ->values()
            ->all();

        $this->truncateMigrationsTable();

        $this->generateMigrations(['--skip-vendor' => true]);

        $this->refreshDatabase();

        $this->runMigrationsFrom('pgsql', $this->getStorageMigrationsPath());

        $generatedTables = $this->getTableNames();

        sort($tablesWithoutVendors);
        sort($generatedTables);

        $this->assertSame($tablesWithoutVendors, $generatedTables);
    }

    private function verify(callable $migrateTemplates, callable $generateMigrations, ?callable $beforeVerify = null): void
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

    private function assertLineExistsThenReplace(string $file, string $line): void
    {
        $this->assertTrue(
            str_contains(
                File::get($file),
                $line
            )
        );

        File::put(
            $file,
            str_replace(
                $line,
                'replaced',
                File::get($file)
            )
        );
    }
}
