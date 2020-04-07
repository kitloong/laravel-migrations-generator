<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 12:22
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use KitLoong\MigrationsGenerator\Generators\EnumField;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;

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

        /** @var EnumField $enumField */
        $enumField = resolve(EnumField::class);

        $field = [
            'field' => 'enum_field',
            'args' => []
        ];

        $field = $enumField->makeField('table', $field);
        $this->assertSame(["['value1', 'value2' , 'value3']"], $field['args']);
    }

    public function testMakeFieldValueIsEmpty()
    {
        $this->mock(MySQLRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getEnumPresetValues')
                ->with('table', 'enum_field')
                ->andReturnNull()
                ->once();
        });

        /** @var EnumField $enumField */
        $enumField = resolve(EnumField::class);

        $field = [
            'field' => 'enum_field',
            'args' => []
        ];

        $field = $enumField->makeField('table', $field);
        $this->assertEmpty($field['args']);
    }
}
