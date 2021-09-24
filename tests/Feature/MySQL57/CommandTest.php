<?php

namespace Tests\Feature\MySQL57;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use MigrationsGenerator\DBAL\Schema as DBALSchema;

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
            $this->generateMigrations(['--use-db-collation' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testSquashUp()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('mysql57');
        };

        $generateMigrations = function () {
            $this->generateMigrations(['--squash' => true]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testSquashDown()
    {
        $this->migrateGeneral('mysql57');

        $this->truncateMigration();

        $this->generateMigrations(['--squash' => true]);

        $this->rollbackMigrationsFrom('mysql57', $this->storageMigrations());

        $tables = DB::select('SHOW TABLES');
        $this->assertSame(1, count($tables));
        $this->assertSame(0, DB::table('migrations')->count());
    }

    public function testTables()
    {
        $this->migrateGeneral('mysql57');

        $this->truncateMigration();

        $this->generateMigrations(['--tables' => 'all_columns_mysql57,users_mysql57']);

        $this->dropAllTables();

        $this->runMigrationsFrom('mysql57', $this->storageMigrations());

        $tables = DB::select('SHOW TABLES');
        $this->assertSame(3, count($tables));
        $this->assertTrue(Schema::hasTable('all_columns_mysql57'));
        $this->assertTrue(Schema::hasTable('migrations'));
        $this->assertTrue(Schema::hasTable('users_mysql57'));
    }

    public function testIgnore()
    {
        $this->migrateGeneral('mysql57');

        $this->truncateMigration();

        $this->generateMigrations(['--ignore' => 'failed_jobs_mysql57,reserved_name_not_null_mysql57,reserved_name_with_precision_mysql57']);

        $this->dropAllTables();

        $this->runMigrationsFrom('mysql57', $this->storageMigrations());

        $tables = DB::select('SHOW TABLES');
        $this->assertSame(6, count($tables));
        $this->assertTrue(Schema::hasTable('all_columns_mysql57'));
        $this->assertTrue(Schema::hasTable('migrations'));
        $this->assertTrue(Schema::hasTable('test_index_mysql57'));
        $this->assertTrue(Schema::hasTable('users_mysql57'));
        $this->assertTrue(Schema::hasTable('composite_primary_mysql57'));
        $this->assertTrue(Schema::hasTable('user_profile_mysql57'));
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function testDefaultIndexNames()
    {
        $this->migrateGeneral('mysql57');

        $this->truncateMigration();

        $this->generateMigrations([
            '--tables'              => 'test_index_mysql57',
            '--default-index-names' => true
        ]);

        $this->dropAllTables();

        $this->runMigrationsFrom('mysql57', $this->storageMigrations());

        $indexes    = app(DBALSchema::class)->getIndexes('test_index_mysql57');
        $indexNames = array_keys($indexes);
        sort($indexNames);
        $this->assertSame(
            [
                'primary',
                'test_index_mysql57_code_email_index',
                'test_index_mysql57_code_enum_index',
                'test_index_mysql57_code_index',
                'test_index_mysql57_column_hyphen_index',
                'test_index_mysql57_custom_name_index',
                'test_index_mysql57_email_unique',
                'test_index_mysql57_enum_code_unique',
                'test_index_mysql57_line_string_spatialindex',
            ],
            $indexNames
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function testDefaultFKNames()
    {
        $this->migrateGeneral('mysql57');

        $this->truncateMigration();

        $this->generateMigrations(['--default-fk-names' => true]);

        $this->dropAllTables();

        $this->runMigrationsFrom('mysql57', $this->storageMigrations());

        $foreignKeyArray = app(DBALSchema::class)->getForeignKeys('user_profile_mysql57');
        $foreignKeys     = new Collection($foreignKeyArray);
        $foreignKeyNames = $foreignKeys->map(function (ForeignKeyConstraint $foreignKey) {
            return $foreignKey->getName();
        })->sort()->toArray();

        $this->assertSame(
            [
                'user_profile_mysql57_column_hyphen_foreign',
                'user_profile_mysql57_constraint_foreign',
                'user_profile_mysql57_custom_name_foreign',
                'user_profile_mysql57_user_id_foreign',
                'user_profile_mysql57_user_id_sub_id_foreign',
            ],
            $foreignKeyNames
        );

        $this->rollbackMigrationsFrom('mysql57', $this->storageMigrations());
    }

    public function testDate()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('mysql57');
        };

        $generateMigrations = function () {
            $this->generateMigrations([
                '--date' => '2021-10-08 09:30:40',
            ]);
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    public function testTableFilename()
    {
        $this->migrateGeneral('mysql57');

        $this->truncateMigration();

        $this->generateMigrations(['--table-filename' => '[datetime_prefix]_custom_[table]_table.php']);

        $migrations = [];
        foreach (File::files($this->storageMigrations()) as $migration) {
            $migrations[] = substr($migration->getFilenameWithoutExtension(), 18);
        }

        $this->assertSame('custom_all_columns_mysql57_table', $migrations[0]);
    }

    public function testFKFilename()
    {
        $this->migrateGeneral('mysql57');

        $this->truncateMigration();

        $this->generateMigrations(['--fk-filename' => '[datetime_prefix]_custom_[table]_table.php']);

        $migrations = [];
        foreach (File::files($this->storageMigrations()) as $migration) {
            $migrations[] = substr($migration->getFilenameWithoutExtension(), 18);
        }

        $this->assertSame('custom_user_profile_mysql57_table', $migrations[count($migrations) - 1]);
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
