<?php

namespace Tests;

use MigrationsGenerator\Generators\Blueprint\SchemaBlueprint;
use MigrationsGenerator\Generators\Blueprint\TableBlueprint;
use MigrationsGenerator\Generators\MigrationConstants\Method\SchemaBuilder;
use MigrationsGenerator\Generators\TableNameGenerator;
use MigrationsGenerator\Generators\Writer\MigrationWriter;
use Mockery\MockInterface;

class MigrationWriterTest extends TestCase
{
    public function testWrite()
    {
        $this->mock(TableNameGenerator::class, function (MockInterface $mock) {
            $mock->shouldReceive('stripPrefix')
                ->andReturn('test');
        });

        $up        = new SchemaBlueprint('mysql', 'users', SchemaBuilder::CREATE);
        $blueprint = new TableBlueprint();
        $blueprint->setProperty('collation', 'utf-8');
        $blueprint->setProperty('something', 1);
        $blueprint->setProperty('something', true);
        $blueprint->setProperty('something', false);
        $blueprint->setProperty('something', null);
        $blueprint->setProperty('something', [1, 2, 3, 'abc', null, true, false, ['a', 2, 'c']]);
        $blueprint->setLineBreak();
        $blueprint->setMethodByName('string', 'name', 100)
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
