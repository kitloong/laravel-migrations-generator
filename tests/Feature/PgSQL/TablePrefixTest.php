<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\PgSQL;

use Illuminate\Support\Facades\DB;

class TablePrefixTest extends PgSQLTestCase
{
    /**
     * @inheritDoc
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.connections.pgsql.prefix', 'kit_');
    }

    public function testTablePrefix(): void
    {
        $migrateTemplates = function (): void {
            $this->migrateGeneral('pgsql');

            DB::statement(
                "ALTER TABLE kit_all_columns_pgsql ADD COLUMN status my_status NOT NULL",
            );
        };

        $generateMigrations = function (): void {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    private function verify(callable $migrateTemplates, callable $generateMigrations): void
    {
        $migrateTemplates();

        $this->truncateMigrationsTable();
        $this->dumpSchemaAs($this->getStorageSqlPath('expected.sql'));

        $generateMigrations();

        $this->assertMigrations();

        $this->refreshDatabase();

        $this->runMigrationsFrom('pgsql', $this->getStorageMigrationsPath());

        $this->truncateMigrationsTable();
        $this->dumpSchemaAs($this->getStorageSqlPath('actual.sql'));

        $this->assertFileEqualsIgnoringOrder(
            $this->getStorageSqlPath('expected.sql'),
            $this->getStorageSqlPath('actual.sql'),
        );
    }
}
