<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 18:55
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\DatetimeField;
use KitLoong\MigrationsGenerator\Generators\Decorator;
use KitLoong\MigrationsGenerator\Generators\Modifier\DefaultModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\Types\DBALTypes;
use Mockery;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;

class DefaultModifierTest extends TestCase
{
    public function testGenerateFromNumeric()
    {
        /** @var DefaultModifier $defaultModifier */
        $defaultModifier = resolve(DefaultModifier::class);

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getDefault')
            ->andReturn(10);

        $types = [
            DBALTypes::SMALLINT,
            DBALTypes::INTEGER,
            DBALTypes::BIGINT,
            DBALTypes::MEDIUMINT,
            DBALTypes::DECIMAL,
            DBALTypes::FLOAT,
            DBALTypes::DOUBLE
        ];

        foreach ($types as $type) {
            $result = $defaultModifier->generate($type, $column);
            $this->assertSame('default(10)', $result);
        }
    }

    public function testGenerateFromDatetime()
    {
        $column = Mockery::mock(Column::class);

        $types = [
            DBALTypes::DATETIME_MUTABLE,
            DBALTypes::TIMESTAMP
        ];


        $this->mock(DatetimeField::class, function (MockInterface $mock) use ($column) {
            $mock->shouldReceive('makeDefault')
                ->with($column)
                ->andReturn('default');
        });

        /** @var DefaultModifier $defaultModifier */
        $defaultModifier = resolve(DefaultModifier::class);

        foreach ($types as $type) {
            $result = $defaultModifier->generate($type, $column);
            $this->assertSame('default', $result);
        }
    }

    public function testGenerateDefault()
    {
        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getDefault')
            ->andReturn('value');

        $this->mock(Decorator::class, function (MockInterface $mock) use ($column) {
            $mock->shouldReceive('columnDefaultToString')
                ->with('value')
                ->andReturn('defaultValue');

            $mock->shouldReceive('decorate')
                ->with(ColumnModifier::DEFAULT, ['defaultValue'])
                ->andReturn('decoratedValue');
        });

        /** @var DefaultModifier $defaultModifier */
        $defaultModifier = resolve(DefaultModifier::class);

        $result = $defaultModifier->generate('string', $column);
        $this->assertSame('decoratedValue', $result);
    }
}
