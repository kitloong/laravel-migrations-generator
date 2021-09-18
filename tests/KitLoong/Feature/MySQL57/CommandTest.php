<?php

namespace Tests\KitLoong\Feature\MySQL57;

use Illuminate\Support\Facades\DB;

/**
 * @runTestsInSeparateProcesses
 */
class CommandTest extends MySQL57TestCase
{
    public function testRun()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('mysql57');
        };

        $generateMigrations = function () {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testDown()
    {
        $this->migrateGeneral('mysql57');

        $this->truncateMigration();

        $this->generateMigrations();

        $this->rollbackMigrationsFrom('mysql57', $this->storageMigrations());

        $tables = DB::select('SHOW TABLES');
        $this->assertSame(1, count($tables));
        $this->assertSame(0, DB::table('migrations')->count());
    }

    public function testCollation()
    {
        $migrateTemplates = function () {
            $this->migrateCollation('mysql57');
        };

        $generateMigrations = function () {
            $this->generateMigrations(['--useDBCollation' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testSquashUp()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('mysql57');
        };

        $generateMigrations = function () {
            $this->generateMigrations([
                '--squash' => true
            ]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testSquashDown()
    {
        $this->migrateGeneral('mysql57');

        $this->truncateMigration();

        $this->generateMigrations([
            '--squash' => true
        ]);

        $this->rollbackMigrationsFrom('mysql57', $this->storageMigrations());

        $tables = DB::select('SHOW TABLES');
        $this->assertSame(1, count($tables));
        $this->assertSame(0, DB::table('migrations')->count());
    }

    private function verify(callable $migrateTemplates, callable $generateMigrations)
    {
        $migrateTemplates();

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('expected.sql'));

        $generateMigrations();

        $this->assertMigrations();

        $this->dropAllTables();

        $this->runMigrationsFrom('mysql57', $this->storageMigrations());

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('actual.sql'));

        $this->assertFileEqualsIgnoringOrder(
            $this->storageSql('expected.sql'),
            $this->storageSql('actual.sql')
        );
    }
}
