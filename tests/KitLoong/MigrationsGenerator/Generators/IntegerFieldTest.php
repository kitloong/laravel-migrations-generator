<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 12:33
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\IntegerField;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use Mockery;
use Orchestra\Testbench\TestCase;

class IntegerFieldTest extends TestCase
{
    public function testMakeFieldIsIncrements()
    {
        /** @var IntegerField $integerField */
        $integerField = resolve(IntegerField::class);

        $indexes = collect(['field' => 'index']);

        $field = [
            'field' => 'field',
            'type' => 'integer',
            'args' => [],
            'decorators' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getUnsigned')
            ->andReturnTrue();
        $column->shouldReceive('getAutoincrement')
            ->andReturnTrue();

        $field = $integerField->makeField($field, $column, $indexes);
        $this->assertSame(ColumnType::INCREMENTS, $field['type']);
        $this->assertEmpty($indexes);
        $this->assertEmpty($field['args']);
        $this->assertEmpty($field['decorators']);

        $field = [
            'field' => 'field',
            'type' => 'bigint',
            'args' => []
        ];

        $field = $integerField->makeField($field, $column, $indexes);
        $this->assertSame(ColumnType::BIG_INCREMENTS, $field['type']);
    }

    public function testMakeFieldIsUnsinged()
    {
        /** @var IntegerField $integerField */
        $integerField = resolve(IntegerField::class);

        $field = [
            'field' => 'field',
            'type' => 'smallint',
            'args' => [],
            'decorators' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getUnsigned')
            ->andReturnTrue();
        $column->shouldReceive('getAutoincrement')
            ->andReturnFalse();

        $field = $integerField->makeField($field, $column, collect());
        $this->assertSame(ColumnType::SMALL_INTEGER, $field['type']);
        $this->assertSame([ColumnModifier::UNSIGNED], $field['decorators']);
        $this->assertEmpty($field['args']);
    }

    public function testMakeFieldIsAutoIncrement()
    {
        /** @var IntegerField $integerField */
        $integerField = resolve(IntegerField::class);

        $indexes = collect(['field' => 'index']);

        $field = [
            'field' => 'field',
            'type' => 'mediumint',
            'args' => [],
            'decorators' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getUnsigned')
            ->andReturnFalse();
        $column->shouldReceive('getAutoincrement')
            ->andReturnTrue();

        $field = $integerField->makeField($field, $column, $indexes);
        $this->assertSame(ColumnType::MEDIUM_INTEGER, $field['type']);
        $this->assertSame(['true'], $field['args']);
        $this->assertEmpty($field['decorators']);
        $this->assertEmpty($indexes);
    }
}
