<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/06
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\BooleanField;
use Mockery;
use Orchestra\Testbench\TestCase;

class BooleanFieldTest extends TestCase
{
    public function testMakeDefault()
    {
        /** @var BooleanField $booleanField */
        $booleanField = resolve(BooleanField::class);

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getDefault')
            ->andReturn(false);

        $this->assertSame(0, $booleanField->makeDefault($column));

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getDefault')
            ->andReturn(true);

        $this->assertSame(1, $booleanField->makeDefault($column));
    }
}
