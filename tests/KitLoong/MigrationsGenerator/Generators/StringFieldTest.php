<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\StringField;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use Mockery;
use Orchestra\Testbench\TestCase;

class StringFieldTest extends TestCase
{
    public function testMakeFieldIsChar()
    {
        /** @var StringField $stringField */
        $stringField = resolve(StringField::class);

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

        $field = $stringField->makeField($field, $column);
        $this->assertSame([
            'field' => 'field',
            'type' => ColumnType::CHAR,
            'args' => [50]
        ], $field);
    }

    public function testMakeFieldIsRememberToken()
    {
        /** @var StringField $stringField */
        $stringField = resolve(StringField::class);

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

        $field = $stringField->makeField($field, $column);
        $this->assertSame(ColumnType::REMEMBER_TOKEN, $field['type']);
        $this->assertNull($field['field']);
        $this->assertEmpty($field['args']);
    }

    public function testMakeFieldWith255Length()
    {
        /** @var StringField $stringField */
        $stringField = resolve(StringField::class);

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

        $field = $stringField->makeField($field, $column);
        $this->assertSame('string', $field['type']);
        $this->assertSame('field', $field['field']);
        $this->assertEmpty($field['args']);
    }

    public function testMakeFieldWith100Length()
    {
        /** @var StringField $stringField */
        $stringField = resolve(StringField::class);

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

        $field = $stringField->makeField($field, $column);
        $this->assertSame('string', $field['type']);
        $this->assertSame('field', $field['field']);
        $this->assertSame([100], $field['args']);
    }
}
