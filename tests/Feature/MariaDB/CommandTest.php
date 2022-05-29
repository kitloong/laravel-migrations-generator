<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\MariaDB;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Support\CheckMigrationMethod;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CommandTest extends MariaDBTestCase
{
    use CheckMigrationMethod;

    public function testRun()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('mariadb');
        };

        $generateMigrations = function () {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testDown()
    {
        $this->migrateGeneral('mariadb');

        $this->truncateMigration();

        $this->generateMigrations();

        $this->rollbackMigrationsFrom('mariadb', $this->storageMigrations());

        $tables = $this->getTableNames();
        $views  = $this->getViewNames();

        $this->assertCount(1, $tables);
        $this->assertCount(0, $views);
        $this->assertSame(0, DB::table('migrations')->count());
    }

    public function testCollation()
    {
        $migrateTemplates = function () {
            $this->migrateCollation('mariadb');
        };

        $generateMigrations = function () {
            $this->generateMigrations(['--use-db-collation' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    private function verify(callable $migrateTemplates, callable $generateMigrations)
    {
        $migrateTemplates();

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('expected.sql'));

        $generateMigrations();

        $this->assertMigrations();

        $this->dropAllTables();

        $this->runMigrationsFrom('mariadb', $this->storageMigrations());

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('actual.sql'));

        $this->assertFileEqualsIgnoringOrder(
            $this->storageSql('expected.sql'),
            $this->storageSql('actual.sql')
        );
    }
}
