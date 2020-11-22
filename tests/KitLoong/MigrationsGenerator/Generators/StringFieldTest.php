<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\Modifier\CollationModifier;
use KitLoong\MigrationsGenerator\Generators\StringField;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use Mockery;
use Mockery\MockInterface;
use Tests\KitLoong\TestCase;

class StringFieldTest extends TestCase
{
    public function testMakeFieldIsChar()
    {
        $field = [
            'field' => 'field',
            'type' => 'string',
            'args' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getFixed')
            ->andReturnTrue();
        $column->shouldReceive('getLength')
            ->andReturn(50);

        $this->mock(CollationModifier::class, function (MockInterface $mock) use ($column) {
            $mock->shouldReceive('generate')
                ->with('table', $column)
                ->andReturn('collation')
                ->once();
        });

        /** @var StringField $stringField */
        $stringField = app(StringField::class);

        $output = $stringField->makeField('table', $field, $column);

        $this->assertSame([
            'field' => 'field',
            'type' => ColumnType::CHAR,
            'args' => [50],
            'decorators' => ['collation']
        ], $output);
    }

    public function testMakeFieldIsRememberToken()
    {
        $field = [
            'field' => 'remember_token',
            'type' => 'string',
            'args' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getFixed')
            ->andReturnFalse();
        $column->shouldReceive('getLength')
            ->andReturn(100);

        $this->mock(CollationModifier::class, function (MockInterface $mock) use ($column) {
            $mock->shouldReceive('generate')
                ->with('table', $column)
                ->andReturn('collation')
                ->once();
        });

        /** @var StringField $stringField */
        $stringField = app(StringField::class);

        $field = $stringField->makeField('table', $field, $column);
        $this->assertSame(ColumnType::REMEMBER_TOKEN, $field['type']);
        $this->assertNull($field['field']);
        $this->assertEmpty($field['args']);
        $this->assertSame(['collation'], $field['decorators']);
    }

    public function testMakeFieldWith255Length()
    {
        $field = [
            'field' => 'field',
            'type' => 'string',
            'args' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getFixed')
            ->andReturnFalse();
        $column->shouldReceive('getLength')
            ->andReturn(255);

        $this->mock(CollationModifier::class, function (MockInterface $mock) use ($column) {
            $mock->shouldReceive('generate')
                ->with('table', $column)
                ->andReturn('collation')
                ->once();
        });

        /** @var StringField $stringField */
        $stringField = app(StringField::class);

        $field = $stringField->makeField('table', $field, $column);
        $this->assertSame('string', $field['type']);
        $this->assertSame('field', $field['field']);
        $this->assertEmpty($field['args']);
    }

    public function testMakeFieldWith100Length()
    {
        $field = [
            'field' => 'field',
            'type' => 'string',
            'args' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getFixed')
            ->andReturnFalse();
        $column->shouldReceive('getLength')
            ->andReturn(100);

        $this->mock(CollationModifier::class, function (MockInterface $mock) use ($column) {
            $mock->shouldReceive('generate')
                ->with('table', $column)
                ->andReturn('collation')
                ->once();
        });

        /** @var StringField $stringField */
        $stringField = app(StringField::class);

        $field = $stringField->makeField('table', $field, $column);
        $this->assertSame('string', $field['type']);
        $this->assertSame('field', $field['field']);
        $this->assertSame([100], $field['args']);
    }
}
