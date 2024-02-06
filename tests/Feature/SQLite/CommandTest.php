<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\SQLite;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Support\CheckMigrationMethod;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CommandTest extends SQLiteTestCase
{
    use CheckMigrationMethod;

    public function testRun(): void
    {
        $migrateTemplates = function (): void {
            $this->migrateGeneral('sqlite');
        };

        $generateMigrations = function (): void {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testDown(): void
    {
        $this->migrateGeneral('sqlite');

        $this->truncateMigrationsTable();

        $this->generateMigrations();

        $this->rollbackMigrationsFrom('sqlite', $this->getStorageMigrationsPath());

        $tables = $this->getTableNames();
        $views  = $this->getViewNames();

        $this->assertCount(1, $tables);
        $this->assertCount(0, $views);
        $this->assertSame(0, DB::table('migrations')->count());
    }

//    public function testCollation(): void
//    {
//        $migrateTemplates = function (): void {
//            $this->migrateCollation('sqlite');
//        };
//
//        $generateMigrations = function (): void {
//            $this->generateMigrations(['--use-db-collation' => true]);
//        };
//
//        $this->verify($migrateTemplates, $generateMigrations);
//    }

    public function testSkipVendor(): void
    {
        $this->migrateGeneral('sqlite');

        $this->migrateVendors('sqlite');

        // Load migrations from vendors path to mock vendors migration.
        // Loaded migrations should not be generated.
        app('migrator')->path($this->getStorageFromVendorsPath());

        $tables = $this->getTableNames();

        $vendors = [
            'personal_access_tokens_sqlite',
            'telescope_entries_sqlite',
            'telescope_entries_tags_sqlite',
            'telescope_monitoring_sqlite',
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

        $this->runMigrationsFrom('sqlite', $this->getStorageMigrationsPath());

        $generatedTables = $this->getTableNames();

        $this->assertSame($tablesWithoutVendors, $generatedTables);
    }

    private function verify(callable $migrateTemplates, callable $generateMigrations): void
    {
        $migrateTemplates();

        $this->truncateMigrationsTable();
        $this->dumpSchemaAs($this->getStorageSqlPath('expected.sql'));

        $generateMigrations();

        $this->assertMigrations();

        $this->refreshDatabase();

        $this->runMigrationsFrom('sqlite', $this->getStorageMigrationsPath());

        $this->truncateMigrationsTable();
        $this->dumpSchemaAs($this->getStorageSqlPath('actual.sql'));

        $this->assertFileEqualsIgnoringOrder(
            $this->getStorageSqlPath('expected.sql'),
            $this->getStorageSqlPath('actual.sql')
        );
    }
}
