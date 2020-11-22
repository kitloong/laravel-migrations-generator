<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\EnumField;
use KitLoong\MigrationsGenerator\Generators\Modifier\CollationModifier;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;
use Mockery;
use Mockery\MockInterface;
use Tests\KitLoong\TestCase;

class EnumFieldTest extends TestCase
{
    public function testMakeField()
    {
        $this->mock(MySQLRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getEnumPresetValues')
                ->with('table', 'enum_field')
                ->andReturn("['value1', 'value2' , 'value3']")
                ->once();
        });

        $field = [
            'field' => 'enum_field',
            'args' => []
        ];

        $column = Mockery::mock(Column::class);
        $this->mock(CollationModifier::class, function (MockInterface $mock) use ($column) {
            $mock->shouldReceive('generate')
                ->with('table', $column)
                ->andReturn('collation')
                ->once();
        });

        /** @var EnumField $enumField */
        $enumField = resolve(EnumField::class);
        $output = $enumField->makeField('table', $field, $column);
        $this->assertSame([
            'field' => 'enum_field',
            'args' => ["['value1', 'value2' , 'value3']"],
            'decorators' => ['collation']
        ], $output);
    }

    public function testMakeFieldValueIsEmpty()
    {
        $this->mock(MySQLRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getEnumPresetValues')
                ->with('table', 'enum_field')
                ->andReturnNull()
                ->once();
        });

        $field = [
            'field' => 'enum_field',
            'args' => []
        ];

        $column = Mockery::mock(Column::class);
        $this->mock(CollationModifier::class, function (MockInterface $mock) use ($column) {
            $mock->shouldReceive('generate')
                ->with('table', $column)
                ->andReturn('collation')
                ->once();
        });

        /** @var EnumField $enumField */
        $enumField = resolve(EnumField::class);

        $field = $enumField->makeField('table', $field, $column);
        $this->assertEmpty($field['args']);
    }
}
