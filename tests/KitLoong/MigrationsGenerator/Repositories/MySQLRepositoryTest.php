<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/07
 */

namespace Tests\KitLoong\MigrationsGenerator\Repositories;

use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;
use Mockery\MockInterface;
use Tests\KitLoong\TestCase;

class MySQLRepositoryTest extends TestCase
{
    public function testGetEnumPresetValues()
    {
        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getConnection->select')
                ->with("SHOW COLUMNS FROM `table` where Field = 'column' AND Type LIKE 'enum(%'")
                ->andReturn([
                    (object) ['Type' => "enum('value1', 'value2' , 'value3')"]
                ])
                ->once();
        });

        /** @var MySQLRepository $repository */
        $repository = app(MySQLRepository::class);

        $value = $repository->getEnumPresetValues('table', 'column');
        $this->assertSame("['value1', 'value2' , 'value3']", $value);
    }

    public function testGetEnumPresetValuesIsNull()
    {
        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getConnection->select')
                ->with("SHOW COLUMNS FROM `table` where Field = 'column' AND Type LIKE 'enum(%'")
                ->andReturn([])
                ->once();
        });

        /** @var MySQLRepository $repository */
        $repository = app(MySQLRepository::class);

        $value = $repository->getEnumPresetValues('table', 'column');
        $this->assertNull($value);
    }

    public function testGetSetPresetValues()
    {
        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getConnection->select')
                ->with("SHOW COLUMNS FROM `table` where Field = 'column' AND Type LIKE 'set(%'")
                ->andReturn([
                    (object) ['Type' => "set('value1', 'value2' , 'value3')"]
                ])
                ->once();
        });

        /** @var MySQLRepository $repository */
        $repository = app(MySQLRepository::class);

        $value = $repository->getSetPresetValues('table', 'column');
        $this->assertSame("['value1', 'value2' , 'value3']", $value);
    }

    public function testGetSetPresetValuesIsNull()
    {
        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getConnection->select')
                ->with("SHOW COLUMNS FROM `table` where Field = 'column' AND Type LIKE 'set(%'")
                ->andReturn([])
                ->once();
        });

        /** @var MySQLRepository $repository */
        $repository = app(MySQLRepository::class);

        $value = $repository->getSetPresetValues('table', 'column');
        $this->assertNull($value);
    }
}
