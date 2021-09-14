<?php

namespace Tests\KitLoong;

use KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint;
use KitLoong\MigrationsGenerator\Generators\Blueprint\TableBlueprint;
use KitLoong\MigrationsGenerator\Generators\Methods\SchemaBuilder;
use KitLoong\MigrationsGenerator\Generators\Writer\MigrationWriter;

class WriterTest extends TestCase
{
    public function testWrite()
    {
        $this->markTestSkipped();
        $up        = new SchemaBlueprint('mysql', 'users', SchemaBuilder::CREATE);
        $blueprint = new TableBlueprint();
        $blueprint->setProperty('collation', 'utf-8');
        $blueprint->setProperty('something', 1);
        $blueprint->setProperty('something', true);
        $blueprint->setProperty('something', false);
        $blueprint->setProperty('something', null);
        $blueprint->setProperty('something', [1, 2, 3, 'abc', null, true, false, ['a', 2, 'c']]);
        $blueprint->setLineBreak();
        $blueprint->setColumnMethodByName('string', 'name', 100)
            ->chain('comment', 'Hello')
            ->chain('default', 'Test');
        $up->setBlueprint($blueprint);

        $down = new SchemaBlueprint('mysql', 'users', SchemaBuilder::DROP_IF_EXISTS);

        $migration = app(MigrationWriter::class);
        $migration->writeTo(
            storage_path('migration.php'),
            config('generators.config.migration_template_path'),
            'Tester',
            $up,
            $down
        );

        $this->assertFileExists(storage_path('migration.php'));
    }
}
