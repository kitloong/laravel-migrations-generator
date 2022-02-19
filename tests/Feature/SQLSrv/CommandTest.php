<?php

namespace Tests\Feature\SQLSrv;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CommandTest extends SQLSrvTestCase
{
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
        DB::statement("CREATE TABLE custom_sqlsrv (
            money money,
            smallmoney smallmoney,
            [name.dot] varchar(255)
        )");

        $this->generateMigrations();

        // Should generate one migration file only.
        $migration = File::files($this->storageMigrations())[0];

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

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function testDown()
    {
        $this->migrateGeneral('sqlsrv');

        $this->truncateMigration();

        $this->generateMigrations();

        $this->rollbackMigrationsFrom('sqlsrv', $this->storageMigrations());

        $tables = $this->getTableNames();
        $views = $this->getViewNames();

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

        $this->truncateMigration();

        $this->generateMigrations();

        $this->assertTrue(true);
    }

    /**
     * @param  callable  $migrateTemplates
     * @param  callable  $generateMigrations
     * @throws \Doctrine\DBAL\Exception
     */
    public function verify(callable $migrateTemplates, callable $generateMigrations)
    {
        $migrateTemplates();

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('expected.sql'));

        $generateMigrations();

        $this->assertMigrations();

        $this->dropAllTables();

        $this->runMigrationsFrom('sqlsrv', $this->storageMigrations());

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('actual.sql'));

        $this->assertFileEqualsIgnoringOrder(
            $this->storageSql('expected.sql'),
            $this->storageSql('actual.sql')
        );
    }
}
