<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\Modifier\CollationModifier;
use KitLoong\MigrationsGenerator\Generators\OtherField;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use Mockery;
use Mockery\MockInterface;
use Tests\KitLoong\TestCase;

class OtherFieldTest extends TestCase
{
    public function testMakeField()
    {
        $column = Mockery::mock(Column::class);
        $this->mock(CollationModifier::class, function (MockInterface $mock) use ($column) {
            $mock->shouldReceive('generate')
                ->with('table', $column)
                ->andReturn('collation')
                ->once();
        });

        /** @var OtherField $otherField */
        $otherField = app(OtherField::class);

        $field = [
            'field' => 'field',
            'type' => 'blob'
        ];

        $output = $otherField->makeField('table', $field, $column);
        $this->assertSame([
            'field' => 'field',
            'type' => ColumnType::BINARY,
            'decorators' => ['collation']
        ], $output);
    }
}
