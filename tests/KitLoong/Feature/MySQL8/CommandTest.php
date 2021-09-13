<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/11/14
 */

namespace Tests\KitLoong\Feature\MySQL8;

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

    public function testCollation()
    {
        $migrateTemplates = function () {
            $this->migrateCollation('mysql8');
        };

        $generateMigrations = function () {
            $this->generateMigrations(['--useDBCollation' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    private function verify(callable $migrateTemplates, callable $generateMigrations)
    {
        $migrateTemplates();

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('expected.sql'));

        $generateMigrations();

        $this->dropAllTables();

        $this->loadMigrationsFrom($this->storageMigrations());

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('actual.sql'));

        $this->assertFileEqualsIgnoringOrder(
            $this->storageSql('expected.sql'),
            $this->storageSql('actual.sql')
        );
    }
}
