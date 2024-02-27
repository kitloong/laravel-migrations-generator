<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\SQLite;

use Illuminate\Support\Facades\DB;

class TablePrefixTest extends SQLiteTestCase
{
    public function testTablePrefix(): void
    {
        $migrateTemplates = function (): void {
            $this->migrateGeneral();
        };

        $generateMigrations = function (): void {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    /**
     * @inheritDoc
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.connections.sqlite.prefix', 'prefix_');
    }

    private function verify(callable $migrateTemplates, callable $generateMigrations): void
    {
        $migrateTemplates();

        $this->truncateMigrationsTable();
        DB::statement("delete from sqlite_sequence where name = 'prefix_migrations'");

        $this->dumpSchemaAs($this->getStorageSqlPath('expected.sql'));
        $generateMigrations();

        $this->assertMigrations();

        $this->refreshDatabase();

        $this->runMigrationsFrom($this->getStorageMigrationsPath());

        $this->truncateMigrationsTable();
        DB::statement("delete from sqlite_sequence where name = 'prefix_migrations'");

        $this->dumpSchemaAs($this->getStorageSqlPath('actual.sql'));

        $this->assertFileEqualsIgnoringOrder(
            $this->getStorageSqlPath('expected.sql'),
            $this->getStorageSqlPath('actual.sql'),
        );
    }
}
