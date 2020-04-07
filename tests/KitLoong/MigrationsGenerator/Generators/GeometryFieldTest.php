<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/07
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use KitLoong\MigrationsGenerator\Generators\GeometryField;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\MigrationGeneratorSetting;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\MigrationMethod\PgSQLGeography;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Types\DBALTypes;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;

class GeometryFieldTest extends TestCase
{
    public function testMakeFieldFromPgSQL()
    {
        $this->mock(MigrationGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPlatform')
                ->andReturn(Platform::POSTGRESQL);
        });

        $types = PgSQLGeography::MAP;

        foreach ($types as $pgsqlType => $methodType) {
            $field = [
                'field' => 'field',
                'type' => DBALTypes::GEOMETRY,
                'args' => [],
                'decorators' => []
            ];

            $this->mock(PgSQLRepository::class, function (MockInterface $mock) use ($pgsqlType) {
                $mock->shouldReceive('getTypeByColumnName')
                    ->with('table', 'field')
                    ->andReturn($pgsqlType)
                    ->once();
            });

            /** @var GeometryField $geometryField */
            $geometryField = app(GeometryField::class);

            $field = $geometryField->makeField('table', $field);
            $this->assertSame([
                'field' => 'field',
                'type' => $methodType,
                'args' => [],
                'decorators' => []
            ], $field);
        }
    }

    public function testMakeFieldFromPgSQLFormat()
    {
        $this->mock(MigrationGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPlatform')
                ->andReturn(Platform::POSTGRESQL);
        });

        $field = [
            'field' => 'field',
            'type' => DBALTypes::GEOMETRY,
            'args' => [],
            'decorators' => []
        ];

        $this->mock(PgSQLRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getTypeByColumnName')
                ->with('table', 'field')
                ->andReturn('geography(GeometryCollection, 4326)')
                ->once();
        });

        /** @var GeometryField $geometryField */
        $geometryField = app(GeometryField::class);

        $field = $geometryField->makeField('table', $field);
        $this->assertSame([
            'field' => 'field',
            'type' => ColumnType::GEOMETRY_COLLECTION,
            'args' => [],
            'decorators' => []
        ], $field);
    }
}
