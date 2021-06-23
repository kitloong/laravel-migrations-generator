<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/11/14
 */

namespace Tests\KitLoong\Feature\PgSQL;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CommandTest extends PgSQLTestCase
{
    public function testRun()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('pgsql');
        };

        $generateMigrations = function () {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testCollation()
    {
        $migrateTemplates = function () {
            $this->migrateCollation('pgsql');
        };

        $generateMigrations = function () {
            $this->generateMigrations(['--followCollation' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function verify(callable $migrateTemplates, callable $generateMigrations)
    {
        $migrateTemplates();

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('expected.sql'));

        $generateMigrations();

        foreach (File::files($this->storageMigrations()) as $file) {
            if (Str::contains($file->getBasename(), 'tiger')) {
                File::delete($file);
            }

            if (Str::contains($file->getBasename(), 'topology')) {
                File::delete($file);
            }
        }

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
