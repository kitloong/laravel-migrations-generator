<?php

namespace Tests\Feature\PgSQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CommandTest extends PgSQLTestCase
{
    /**
     * @throws \Doctrine\DBAL\Exception
     */
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

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function testCollation()
    {
        $migrateTemplates = function () {
            $this->migrateCollation('pgsql');
        };

        $generateMigrations = function () {
            $this->generateMigrations(['--use-db-collation' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function testIgnore()
    {
        $this->migrateGeneral('pgsql');

        $this->truncateMigration();

        $this->generateMigrations([
            '--ignore' => implode(',', [
                'name-with-hyphen-pgsql',
                'name-with-hyphen-pgsql_view',
            ])
        ]);

        $this->dropAllTables();

        $this->runMigrationsFrom('pgsql', $this->storageMigrations());

        $tables = $this->getTableNames();
        $views  = $this->getViewNames();

        $this->assertNotContains('name-with-hyphen-pgsql', $tables);
        $this->assertNotContains('public."name-with-hyphen-pgsql_view"', $views);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function verify(callable $migrateTemplates, callable $generateMigrations, callable $beforeVerify = null)
    {
        $migrateTemplates();

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('expected.sql'));

        $generateMigrations();

        $this->assertMigrations();

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
