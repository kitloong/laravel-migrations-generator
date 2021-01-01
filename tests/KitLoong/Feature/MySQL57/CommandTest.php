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
        $this->loadMigrationsFrom(base_path('tests/KitLoong/resources/database/migrations'));

        $this->dumpSchemaAs($this->sqlOutputPath('expected.sql'));

        $this->generateMigrations();

        $this->dropAllTables();

        $this->loadMigrationsFrom($this->migrationOutputPath());

        $this->dumpSchemaAs($this->sqlOutputPath('actual.sql'));

        $this->assertFileEquals(
            $this->sqlOutputPath('expected.sql'),
            $this->sqlOutputPath('actual.sql')
        );
    }
}
