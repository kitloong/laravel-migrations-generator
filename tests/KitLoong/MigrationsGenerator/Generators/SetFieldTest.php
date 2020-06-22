<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use KitLoong\MigrationsGenerator\Generators\SetField;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;

class SetFieldTest extends TestCase
{
    public function testMakeField()
    {
        $this->mock(MySQLRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getSetPresetValues')
                ->with('table', 'set_field')
                ->andReturn("['value1', 'value2' , 'value3']")
                ->once();
        });

        /** @var SetField $setField */
        $setField = resolve(SetField::class);

        $field = [
            'field' => 'set_field',
            'args' => []
        ];

        $field = $setField->makeField('table', $field);
        $this->assertSame(["['value1', 'value2' , 'value3']"], $field['args']);
    }

    public function testMakeFieldValueIsEmpty()
    {
        $this->mock(MySQLRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getSetPresetValues')
                ->with('table', 'set_field')
                ->andReturnNull()
                ->once();
        });

        /** @var SetField $setField */
        $setField = resolve(SetField::class);

        $field = [
            'field' => 'set_field',
            'args' => []
        ];

        $field = $setField->makeField('table', $field);
        $this->assertEmpty($field['args']);
    }
}
