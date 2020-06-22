<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 12:09
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\DecimalField;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\Types\DBALTypes;
use Mockery;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;

class DecimalFieldTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPlatform')->andReturn(Platform::MYSQL);
        });
    }

    public function testMakeField()
    {
        /** @var DecimalField $decimalField */
        $decimalField = resolve(DecimalField::class);

        $field = [
            'field' => 'field',
            'type' => DBALTypes::DECIMAL,
            'args' => [],
            'decorators' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getPrecision')
            ->andReturn(5);
        $column->shouldReceive('getScale')
            ->andReturn(3);

        $column->shouldReceive('getUnsigned')
            ->andReturnTrue()
            ->once();

        $field = $decimalField->makeField($field, $column);
        $this->assertSame([5, 3], $field['args']);
        $this->assertSame([ColumnModifier::UNSIGNED], $field['decorators']);
    }

    public function testMakeFieldPrecisionIsDefault()
    {
        /** @var DecimalField $decimalField */
        $decimalField = resolve(DecimalField::class);

        $field = [
            'field' => 'field',
            'type' => DBALTypes::FLOAT,
            'args' => [],
            'decorators' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getPrecision')
            ->andReturn(8);
        $column->shouldReceive('getScale')
            ->andReturn(2);

        $column->shouldReceive('getUnsigned')
            ->andReturnFalse();

        $field = $decimalField->makeField($field, $column);
        $this->assertSame([
            'field' => 'field',
            'type' => ColumnType::FLOAT,
            'args' => [],
            'decorators' => []
        ], $field);
    }

    public function testMakeFieldScaleIsDefaultButPrecisionIsNot()
    {
        /** @var DecimalField $decimalField */
        $decimalField = resolve(DecimalField::class);

        $field = [
            'field' => 'field',
            'type' => DBALTypes::DECIMAL,
            'args' => [],
            'decorators' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getPrecision')
            ->andReturn(5);
        $column->shouldReceive('getScale')
            ->andReturn(2);

        $column->shouldReceive('getUnsigned')
            ->andReturnFalse();

        $field = $decimalField->makeField($field, $column);
        $this->assertSame([5], $field['args']);
    }

    public function testMakeFieldDoubleShouldReturnEmptyPrecision()
    {
        /** @var DecimalField $decimalField */
        $decimalField = resolve(DecimalField::class);

        $field = [
            'field' => 'field',
            'type' => DBALTypes::DOUBLE,
            'args' => [],
            'decorators' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getPrecision')
            ->andReturn(10);
        $column->shouldReceive('getScale')
            ->andReturn(0);
        $column->shouldReceive('getUnsigned')
            ->andReturnTrue();

        $field = $decimalField->makeField($field, $column);
        $this->assertSame([
            'field' => 'field',
            'type' => ColumnType::DOUBLE,
            'args' => [],
            'decorators' => [ColumnModifier::UNSIGNED]
        ], $field);
    }
}
