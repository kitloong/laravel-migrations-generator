<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\SQLSrv;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CommandTest extends SQLSrvTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Method `typeDateTime` is using different implementation since v5.8
        // It is hard to create unified UT across Laravel <= v5.7 and >= v5.8
        // To simplify, dropping UT check for version <= 5.7.
        // https://github.com/laravel/framework/blob/5.7/src/Illuminate/Database/Schema/Grammars/SqlServerGrammar.php#L523
        // https://github.com/laravel/framework/blob/5.8/src/Illuminate/Database/Schema/Grammars/SqlServerGrammar.php#L538
        if ($this->atLeastLaravel5Dot8()) {
            return;
        }

        $this->markTestSkipped();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function testRun(): void
    {
        $migrateTemplates = function (): void {
            $this->migrateGeneral('sqlsrv');

            DB::statement(
                "ALTER TABLE all_columns_sqlsrv ADD accountnumber accountnumber NOT NULL"
            );
        };

        $generateMigrations = function (): void {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testUnsupportedColumns(): void
    {
        DB::statement(
            "CREATE TABLE custom_sqlsrv (
                money money,
                smallmoney smallmoney,
                [name.dot] varchar(255)
            )"
        );

        $this->generateMigrations();

        // Should generate one migration file only.
        $migration = File::files($this->getStorageMigrationsPath())[0];

        $this->assertStringContainsString(
            '$table->decimal(\'money\', 19, 4)->nullable();',
            $migration->getContents()
        );

        $this->assertStringContainsString(
            '$table->decimal(\'smallmoney\', 10, 4)->nullable();',
            $migration->getContents()
        );

        $this->assertStringContainsString(
            '$table->string(\'name.dot\')->nullable()',
            $migration->getContents()
        );
    }

    public function testDown(): void
    {
        $this->migrateGeneral('sqlsrv');

        $this->truncateMigrationsTable();

        $this->generateMigrations();

        $this->rollbackMigrationsFrom('sqlsrv', $this->getStorageMigrationsPath());

        $tables = $this->getTableNames();
        $views  = $this->getViewNames();

        $this->assertCount(1, $tables);
        $this->assertCount(0, $views);
        $this->assertSame(0, DB::table('migrations')->count());
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function testCollation(): void
    {
        $migrateTemplates = function (): void {
            $this->migrateCollation('sqlsrv');
        };

        $generateMigrations = function (): void {
            $this->generateMigrations(['--use-db-collation' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testGenerateXml(): void
    {
        $this->migrateGeneral('sqlsrv');

        // Test xml column
        DB::statement("alter table all_columns_sqlsrv add xml xml");

        $this->truncateMigrationsTable();

        $this->generateMigrations();

        $this->assertTrue(true);
    }

    public function testSkipVendor(): void
    {
        $this->migrateGeneral('sqlsrv');

        $this->migrateVendors('sqlsrv');

        // Load migrations from vendors path to mock vendors migration.
        // Loaded migrations should not be generated.
        app('migrator')->path($this->getStorageFromVendorsPath());

        $tables = $this->getTableNames();

        $vendors = [
            'personal_access_tokens_sqlsrv',
            'telescope_entries_sqlsrv',
            'telescope_entries_tags_sqlsrv',
            'telescope_monitoring_sqlsrv',
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

        $this->runMigrationsFrom('sqlsrv', $this->getStorageMigrationsPath());

        $generatedTables = $this->getTableNames();

        $this->assertSame($tablesWithoutVendors, $generatedTables);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function verify(callable $migrateTemplates, callable $generateMigrations): void
    {
        $migrateTemplates();

        $this->truncateMigrationsTable();
        $this->dumpSchemaAs($this->getStorageSqlPath('expected.sql'));

        $generateMigrations();

        $this->assertMigrations();

        $this->refreshDatabase();

        $this->runMigrationsFrom('sqlsrv', $this->getStorageMigrationsPath());

        $this->truncateMigrationsTable();
        $this->dumpSchemaAs($this->getStorageSqlPath('actual.sql'));

        $this->assertFileEqualsIgnoringOrder(
            $this->getStorageSqlPath('expected.sql'),
            $this->getStorageSqlPath('actual.sql')
        );
    }
}
