<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/11/14
 */

namespace Tests\KitLoong\Feature\MySQL57;

use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Support\Facades\File;

class CommandTest extends MySQL57TestCase
{
    public function testRun()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('mysql57');
        };

        $generateMigrations = function () {
//            $this->generateMigrations();
            $this->artisan(
                'migrate:generate',
                array_merge([], [
                    '--path' => $this->storageMigrations(),
                ])
            )
                ->expectsQuestion('Do you want to log these migrations in the migrations table? [Y/n] ', 'y')
                ->expectsQuestion('Next Batch Number is: 1. We recommend using Batch Number 0 so that it becomes the "first" migration [Default: 0] ', 0);

            $migrations = [];
            foreach (File::files($this->storageMigrations()) as $migration) {
                $migrations[] = $migration->getFilenameWithoutExtension();
            }

            $dbMigrations = app(MigrationRepositoryInterface::class)->getRan();

            // Both file and DB migrations are sorted by name ascending however the result is slightly different.
            // Use PHP sort here to maintain same ordering.
            sort($migrations);
            sort($dbMigrations);

            $this->assertSame($migrations, $dbMigrations);
        };

        $this->verify($migrateTemplates, $generateMigrations);
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
