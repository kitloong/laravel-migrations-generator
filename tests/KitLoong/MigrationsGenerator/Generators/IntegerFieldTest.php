<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 12:33
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Generators\IntegerField;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\MigrationGeneratorSetting;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\Types\DBALTypes;
use Mockery;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;

class IntegerFieldTest extends TestCase
{
    public function testMakeFieldIsIncrements()
    {
        $this->isPostgreSql();

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

            $field = $integerField->makeField('table', $field, $column, $indexes);
            $this->assertSame($columnType, $field['type']);
            $this->assertEmpty($indexes);
            $this->assertEmpty($field['args']);
            $this->assertEmpty($field['decorators']);
        }
    }

    public function testMakeFieldIsUnsinged()
    {
        $this->isPostgreSql();

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

            $field = $integerField->makeField('table', $field, $column, collect());
            $this->assertSame($columnType, $field['type']);
            $this->assertEmpty($field['args']);
            $this->assertEmpty($field['decorators']);
        }
    }

    public function testMakeFieldIsAutoIncrement()
    {
        $this->isPostgreSql();

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

            $field = $integerField->makeField('table', $field, $column, $indexes);
            $this->assertSame($columnType, $field['type']);
            $this->assertSame(['true'], $field['args']);
            $this->assertEmpty($field['decorators']);
            $this->assertEmpty($indexes);
        }
    }

    public function testMysqlBoolean()
    {
        $this->mock(MigrationGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPlatform')
                ->andReturn(Platform::MYSQL);

            $mock->shouldReceive('getConnection');
        });

        DB::shouldReceive('connection->select')
            ->with("SHOW COLUMNS FROM `table` where Field = 'field' AND Type LIKE 'tinyint(1)%'")
            ->andReturn(['column'])
            ->once();

        /** @var IntegerField $integerField */
        $integerField = resolve(IntegerField::class);

        $field = [
            'field' => 'field',
            'type' => DBALTypes::TINYINT,
            'args' => [],
            'decorators' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getUnsigned')
            ->andReturnTrue();
        $column->shouldReceive('getAutoincrement')
            ->andReturnFalse();

        $field = $integerField->makeField('table', $field, $column, collect([]));
        $this->assertSame([
            'field' => 'field',
            'type' => ColumnType::BOOLEAN,
            'args' => [],
            'decorators' => [ColumnModifier::UNSIGNED]
        ], $field);
    }

    private function isPostgreSql()
    {
        $this->mock(MigrationGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPlatform')
                ->andReturn(Platform::POSTGRESQL);
        });
    }

    private function getIntegerMap(): array
    {
        return [
            DBALTypes::INTEGER => ColumnType::INTEGER,
            DBALTypes::BIGINT => ColumnType::BIG_INTEGER,
            DBALTypes::MEDIUMINT => ColumnType::MEDIUM_INTEGER,
            DBALTypes::SMALLINT => ColumnType::SMALL_INTEGER,
            DBALTypes::TINYINT => ColumnType::TINY_INTEGER
        ];
    }

    private function getIntegerIncrementMap(): array
    {
        return [
            DBALTypes::INTEGER => ColumnType::INCREMENTS,
            DBALTypes::BIGINT => ColumnType::BIG_INCREMENTS,
            DBALTypes::MEDIUMINT => ColumnType::MEDIUM_INCREMENTS,
            DBALTypes::SMALLINT => ColumnType::SMALL_INCREMENTS,
            DBALTypes::TINYINT => ColumnType::TINY_INCREMENTS
        ];
    }

    private function getIntegerUnsignedMap(): array
    {
        return [
            DBALTypes::INTEGER => ColumnType::UNSIGNED_INTEGER,
            DBALTypes::BIGINT => ColumnType::UNSIGNED_BIG_INTEGER,
            DBALTypes::MEDIUMINT => ColumnType::UNSIGNED_MEDIUM_INTEGER,
            DBALTypes::SMALLINT => ColumnType::UNSIGNED_SMALL_INTEGER,
            DBALTypes::TINYINT => ColumnType::UNSIGNED_TINY_INTEGER
        ];
    }
}
