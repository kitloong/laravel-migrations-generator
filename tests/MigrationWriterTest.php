<?php

namespace KitLoong\MigrationsGenerator\Tests;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnModifier;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder;
use KitLoong\MigrationsGenerator\Enum\Migrations\Property\TableProperty;
use KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint;
use KitLoong\MigrationsGenerator\Migration\Blueprint\TableBlueprint;
use KitLoong\MigrationsGenerator\Migration\Enum\MigrationFileType;
use KitLoong\MigrationsGenerator\Migration\Writer\MigrationWriter;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\TableName;
use Mockery\MockInterface;

class MigrationWriterTest extends TestCase
{
    public function testWrite(): void
    {
        $setting = app(Setting::class);
        $setting->setDefaultConnection(DB::getDefaultConnection());
        $setting->setWithHasTable(false);

        $this->mock(TableName::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('stripPrefix')
                ->andReturn('test');
        });

        $up        = new SchemaBlueprint('users', SchemaBuilder::CREATE);
        $blueprint = new TableBlueprint();
        $blueprint->setProperty(TableProperty::COLLATION, 'utf-8');
        $blueprint->setProperty(TableProperty::CHARSET, 1);
        $blueprint->setProperty(TableProperty::CHARSET, true);
        $blueprint->setProperty(TableProperty::CHARSET, false);
        $blueprint->setProperty(TableProperty::CHARSET, null);
        $blueprint->setProperty(TableProperty::CHARSET, [1, 2, 3, 'abc', null, true, false, ['a', 2, 'c']]);
        $blueprint->setLineBreak();
        $blueprint->setMethodByName(ColumnType::STRING, 'name', 100)
            ->chain(ColumnModifier::COMMENT, 'Hello')
            ->chain(ColumnModifier::DEFAULT, 'Test');
        $up->setBlueprint($blueprint);

        $down = new SchemaBlueprint('users', SchemaBuilder::DROP_IF_EXISTS);

        $migration = app(MigrationWriter::class);
        $migration->writeTo(
            storage_path('migration.php'),
            config('migrations-generator.migration_template_path'),
            'Tester',
            new Collection([$up]),
            new Collection([$down]),
            MigrationFileType::TABLE,
        );

        $this->assertFileExists(storage_path('migration.php'));
    }
}
