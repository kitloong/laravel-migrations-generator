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
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\Types\DBALTypes;
use Mockery;
use Orchestra\Testbench\TestCase;

class IntegerFieldTest extends TestCase
{
    public function testMakeFieldIsIncrements()
    {
        /** @var IntegerField $integerField */
        $integerField = resolve(IntegerField::class);

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getUnsigned')
            ->andReturnTrue();
        $column->shouldReceive('getAutoincrement')
            ->andReturnTrue();

        foreach ($this->getIntegerIncrementMap() as $dbalType => $columnType) {
            $indexes = collect(['field' => 'index']);

            $field = [
                'field' => 'field',
                'type' => $dbalType,
                'args' => [],
                'decorators' => []
            ];

            $field = $integerField->makeField($field, $column, $indexes);
            $this->assertSame($columnType, $field['type']);
            $this->assertEmpty($indexes);
            $this->assertEmpty($field['args']);
            $this->assertEmpty($field['decorators']);
        }
    }

    public function testMakeFieldIsUnsinged()
    {
        /** @var IntegerField $integerField */
        $integerField = resolve(IntegerField::class);

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getUnsigned')
            ->andReturnTrue();
        $column->shouldReceive('getAutoincrement')
            ->andReturnFalse();

        foreach ($this->getIntegerUnsignedMap() as $dbalType => $columnType) {
            $field = [
                'field' => 'field',
                'type' => $dbalType,
                'args' => [],
                'decorators' => []
            ];

            $field = $integerField->makeField($field, $column, collect());
            $this->assertSame($columnType, $field['type']);
            $this->assertEmpty($field['args']);
            $this->assertEmpty($field['decorators']);
        }
    }

    public function testMakeFieldIsAutoIncrement()
    {
        /** @var IntegerField $integerField */
        $integerField = resolve(IntegerField::class);

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getUnsigned')
            ->andReturnFalse();
        $column->shouldReceive('getAutoincrement')
            ->andReturnTrue();

        foreach ($this->getIntegerMap() as $dbalType => $columnType) {
            $indexes = collect(['field' => 'index']);

            $field = [
                'field' => 'field',
                'type' => $dbalType,
                'args' => [],
                'decorators' => []
            ];

            $field = $integerField->makeField($field, $column, $indexes);
            $this->assertSame($columnType, $field['type']);
            $this->assertSame(['true'], $field['args']);
            $this->assertEmpty($field['decorators']);
            $this->assertEmpty($indexes);
        }
    }

    private function getIntegerMap(): array
    {
        return [
            DBALTypes::INTEGER => ColumnType::INTEGER,
            DBALTypes::BIGINT => ColumnType::BIG_INTEGER,
            DBALTypes::MEDIUMINT => ColumnType::MEDIUM_INTEGER,
            DBALTypes::SMALLINT => ColumnType::SMALL_INTEGER
        ];
    }

    private function getIntegerIncrementMap(): array
    {
        return [
            DBALTypes::INTEGER => ColumnType::INCREMENTS,
            DBALTypes::BIGINT => ColumnType::BIG_INCREMENTS,
            DBALTypes::MEDIUMINT => ColumnType::MEDIUM_INCREMENTS,
            DBALTypes::SMALLINT => ColumnType::SMALL_INCREMENTS
        ];
    }

    private function getIntegerUnsignedMap(): array
    {
        return [
            DBALTypes::INTEGER => ColumnType::UNSIGNED_INTEGER,
            DBALTypes::BIGINT => ColumnType::UNSIGNED_BIG_INTEGER,
            DBALTypes::MEDIUMINT => ColumnType::UNSIGNED_MEDIUM_INTEGER,
            DBALTypes::SMALLINT => ColumnType::UNSIGNED_SMALL_INTEGER
        ];
    }
}
