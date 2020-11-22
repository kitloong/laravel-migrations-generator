<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\Modifier\CollationModifier;
use KitLoong\MigrationsGenerator\Generators\SetField;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;
use Mockery;
use Mockery\MockInterface;
use Tests\KitLoong\TestCase;

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

        $column = Mockery::mock(Column::class);
        $this->mock(CollationModifier::class, function (MockInterface $mock) use ($column) {
            $mock->shouldReceive('generate')
                ->with('table', $column)
                ->andReturn('collation')
                ->once();
        });

        /** @var SetField $setField */
        $setField = app(SetField::class);

        $field = [
            'field' => 'set_field',
            'args' => []
        ];

        $output = $setField->makeField('table', $field, $column);
        $this->assertSame([
            'field' => 'set_field',
            'args' => ["['value1', 'value2' , 'value3']"],
            'decorators' => ['collation']
        ], $output);
    }

    public function testMakeFieldValueIsEmpty()
    {
        $this->mock(MySQLRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getSetPresetValues')
                ->with('table', 'set_field')
                ->andReturnNull()
                ->once();
        });

        $column = Mockery::mock(Column::class);
        $this->mock(CollationModifier::class, function (MockInterface $mock) use ($column) {
            $mock->shouldReceive('generate')
                ->with('table', $column)
                ->andReturn('collation')
                ->once();
        });

        /** @var SetField $setField */
        $setField = app(SetField::class);

        $field = [
            'field' => 'set_field',
            'args' => []
        ];

        $field = $setField->makeField('table', $field, $column);
        $this->assertEmpty($field['args']);
    }
}
