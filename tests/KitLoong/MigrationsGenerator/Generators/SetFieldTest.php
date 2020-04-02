<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 12:22
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Generators\SetField;
use KitLoong\MigrationsGenerator\MigrationGeneratorSetting;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;

class SetFieldTest extends TestCase
{
    public function testMakeField()
    {
        /** @var SetField $setField */
        $setField = resolve(SetField::class);

        $field = [
            'field' => 'set_field',
            'args' => []
        ];

        $this->mock(MigrationGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getConnection');
        });

        DB::shouldReceive('connection->select')
            ->with("SHOW COLUMNS FROM `table` where Field = 'set_field' AND Type LIKE 'set(%'")
            ->andReturn([
                (object) ['Type' => "set('value1', 'value2' , 'value3')"]
            ]);

        $field = $setField->makeField('table', $field);
        $this->assertSame(["['value1', 'value2' , 'value3']"], $field['args']);
    }
}
