<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/11/14
 */

namespace Tests\Feature\PgSQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CommandTest extends PgSQLTestCase
{
    public function testRun()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('pgsql');

            // Test timestamp default now()
            DB::statement("ALTER TABLE all_columns_pgsql ADD COLUMN timestamp_defaultnow timestamp(0) without time zone DEFAULT now() NOT NULL");
        };

        $generateMigrations = function () {
            $this->generateMigrations();
        };

        $beforeVerify = function () {
            $this->assertLineExistsThenReplace(
                $this->storageSql('actual.sql'),
                'timestamp_defaultnow timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL'
            );

            $this->assertLineExistsThenReplace(
                $this->storageSql('expected.sql'),
                'timestamp_defaultnow timestamp(0) without time zone DEFAULT now() NOT NULL'
            );
        };

        $this->verify($migrateTemplates, $generateMigrations, $beforeVerify);
    }

    public function testCollation()
    {
        $migrateTemplates = function () {
            $this->migrateCollation('pgsql');
        };

        $generateMigrations = function () {
            $this->generateMigrations(['--useDBCollation' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function verify(callable $migrateTemplates, callable $generateMigrations, callable $beforeVerify = null)
    {
        $migrateTemplates();

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('expected.sql'));

        $generateMigrations();

        $this->assertMigrations();

        foreach (File::files($this->storageMigrations()) as $file) {
            if (Str::contains($file->getBasename(), 'tiger')) {
                File::delete($file);
            }

            if (Str::contains($file->getBasename(), 'topology')) {
                File::delete($file);
            }
        }

        $this->dropAllTables();

        $this->runMigrationsFrom('pgsql', $this->storageMigrations());

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('actual.sql'));

        $beforeVerify === null ?: $beforeVerify();

        $this->assertFileEqualsIgnoringOrder(
            $this->storageSql('expected.sql'),
            $this->storageSql('actual.sql')
        );
    }

    private function assertLineExistsThenReplace(string $file, string $line)
    {
        $this->assertTrue(str_contains(
            file_get_contents($file),
            $line
        ));

        File::put($file, str_replace(
            $line,
            'replaced',
            file_get_contents($file)
        ));
    }
}
