<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\MySQL8;

use Illuminate\Support\Facades\DB;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CommandTest extends MySQL8TestCase
{
    public function testRun()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('mysql8');
        };

        $generateMigrations = function () {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testDown()
    {
        $this->migrateGeneral('mysql8');

        $this->truncateMigrationsTable();

        $this->generateMigrations();

        $this->rollbackMigrationsFrom('mysql8', $this->getStorageMigrationsPath());

        $tables = $this->getTableNames();
        $views  = $this->getViewNames();

        $this->assertCount(1, $tables);
        $this->assertCount(0, $views);
        $this->assertSame(0, DB::table('migrations')->count());
    }

    public function testCollation()
    {
        $migrateTemplates = function () {
            $this->migrateCollation('mysql8');
        };

        $generateMigrations = function () {
            $this->generateMigrations(['--use-db-collation' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    private function verify(callable $migrateTemplates, callable $generateMigrations)
    {
        $migrateTemplates();

        $this->truncateMigrationsTable();
        $this->dumpSchemaAs($this->getStorageSqlPath('expected.sql'));

        $generateMigrations();

        $this->assertMigrations();

        $this->dropAllTables();

        $this->runMigrationsFrom('mysql8', $this->getStorageMigrationsPath());

        $this->truncateMigrationsTable();
        $this->dumpSchemaAs($this->getStorageSqlPath('actual.sql'));

        $this->assertFileEqualsIgnoringOrder(
            $this->getStorageSqlPath('expected.sql'),
            $this->getStorageSqlPath('actual.sql')
        );
    }
}
