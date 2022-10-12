<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\SQLSrv;

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
        if (!$this->atLeastLaravel5Dot8()) {
            $this->markTestSkipped();
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function testRun()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('sqlsrv');
        };

        $generateMigrations = function () {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testUnsupportedColumns()
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

    public function testDown()
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
    public function testCollation()
    {
        $migrateTemplates = function () {
            $this->migrateCollation('sqlsrv');
        };

        $generateMigrations = function () {
            $this->generateMigrations(['--use-db-collation' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testGenerateXml()
    {
        $this->migrateGeneral('sqlsrv');

        // Test xml column
        DB::statement("alter table all_columns_sqlsrv add xml xml");

        $this->truncateMigrationsTable();

        $this->generateMigrations();

        $this->assertTrue(true);
    }

    /**
     * @param  callable  $migrateTemplates
     * @param  callable  $generateMigrations
     * @throws \Doctrine\DBAL\Exception
     */
    private function verify(callable $migrateTemplates, callable $generateMigrations)
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
