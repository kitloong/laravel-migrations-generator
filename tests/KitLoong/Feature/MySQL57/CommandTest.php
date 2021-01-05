<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/11/14
 */

namespace Tests\KitLoong\Feature\MySQL57;

class CommandTest extends MySQL57TestCase
{
    public function testRun()
    {
        $this->migrateExpected('mysql57');

        $this->truncateMigration();
        $this->dumpSchemaAs($this->sqlOutputPath('expected.sql'));

        $this->generateMigrations();

        $this->dropAllTables();

        $this->loadMigrationsFrom($this->migrationOutputPath());

        $this->truncateMigration();
        $this->dumpSchemaAs($this->sqlOutputPath('actual.sql'));

        $this->assertFileEqualsIgnoringOrder(
            $this->sqlOutputPath('expected.sql'),
            $this->sqlOutputPath('actual.sql')
        );
    }
}
