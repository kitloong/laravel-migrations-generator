<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\MySQL57;

class TablePrefixTest extends MySQL57TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.connections.mysql57.prefix', 'kit_');
    }

    public function testTablePrefix()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('mysql57');
        };

        $generateMigrations = function () {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    private function verify(callable $migrateTemplates, callable $generateMigrations)
    {
        $migrateTemplates();

        $this->truncateMigrationsTable();
        $this->dumpSchemaAs($this->getStorageSqlPath('expected.sql'));

        $generateMigrations();

        $this->assertMigrations();

        $this->dropAllTables();

        $this->runMigrationsFrom('mysql57', $this->getStorageMigrationsPath());

        $this->truncateMigrationsTable();
        $this->dumpSchemaAs($this->getStorageSqlPath('actual.sql'));

        $this->assertFileEqualsIgnoringOrder(
            $this->getStorageSqlPath('expected.sql'),
            $this->getStorageSqlPath('actual.sql')
        );
    }
}
