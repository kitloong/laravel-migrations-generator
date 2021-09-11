<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\DBAL\Types\DBALTypes;
use KitLoong\MigrationsGenerator\Generators\DecimalField;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use Mockery;
use Mockery\MockInterface;
use Tests\KitLoong\TestCase;

class DecimalFieldPostgreSqlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPlatform')->andReturn(Platform::POSTGRESQL);
        });
    }

    public function testMakeFieldFromDecimal()
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
            ->andReturnFalse();

        $field = $decimalField->makeField($field, $column);
        $this->assertSame([
            'field' => 'field',
            'type' => ColumnType::DECIMAL,
            'args' => [5, 3],
            'decorators' => []
        ], $field);
    }

    public function testMakeFieldFromFloat()
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
            ->andReturn(5);
        $column->shouldReceive('getScale')
            ->andReturn(3);
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
}
