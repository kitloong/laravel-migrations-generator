<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\DatetimeField;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnName;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;
use KitLoong\MigrationsGenerator\Types\DBALTypes;
use Mockery;
use Mockery\MockInterface;
use Tests\KitLoong\TestCase;

class DatetimeFieldTest extends TestCase
{
    public function testMakeFieldIsSoftDeletes()
    {
        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getNotnull')
            ->andReturn(false)
            ->once();
        $column->shouldReceive('getLength')
            ->andReturn(2);
        $column->shouldReceive('getType->getName')
            ->andReturn(DBALTypes::TIMESTAMP)
            ->once();
        $column->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn(ColumnName::DELETED_AT)
            ->once();

        $this->mock(MySQLRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('useOnUpdateCurrentTimestamp')
                ->with('table', ColumnName::DELETED_AT)
                ->andReturnFalse()
                ->once();
        });

        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPlatform')
                ->withNoArgs()
                ->andReturn(Platform::MYSQL)
                ->once();
        });

        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);

        $field = [
            'field' => ColumnName::DELETED_AT,
            'type' => 'timestamp',
            'args' => []
        ];

        $field = $datetimeField->makeField('table', $field, $column, false);
        $this->assertSame(ColumnType::SOFT_DELETES, $field['type']);
        $this->assertSame(ColumnName::DELETED_AT, $field['field']);
        $this->assertSame([2], $field['args']);
    }

    public function testMakeFieldIsTimestamps()
    {
        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getLength')
            ->andReturn(2);
        $column->shouldReceive('getType->getName')
            ->andReturn(DBALTypes::TIMESTAMP)
            ->once();
        $column->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn(ColumnName::UPDATED_AT)
            ->once();

        $this->mock(MySQLRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('useOnUpdateCurrentTimestamp')
                ->with('table', ColumnName::UPDATED_AT)
                ->andReturnFalse()
                ->once();
        });

        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPlatform')
                ->withNoArgs()
                ->andReturn(Platform::MYSQL)
                ->once();
        });

        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);
        $field = [
            'field' => ColumnName::UPDATED_AT,
            'type' => 'timestamp',
            'args' => []
        ];

        $field = $datetimeField->makeField('table', $field, $column, true);
        $this->assertSame(ColumnType::TIMESTAMPS, $field['type']);
        $this->assertNull($field['field']);
        $this->assertSame([2], $field['args']);
    }

    public function testMakeFieldIsDatetime()
    {
        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getLength')
            ->andReturn(2);
        $column->shouldReceive('getType->getName')
            ->andReturn(DBALTypes::DATETIME_MUTABLE)
            ->once();

        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPlatform')
                ->withNoArgs()
                ->andReturn(Platform::MYSQL)
                ->once();
        });

        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);

        $field = [
            'field' => 'date',
            'type' => 'datetime',
            'args' => []
        ];

        $field = $datetimeField->makeField('table', $field, $column, false);
        $this->assertSame(ColumnType::DATETIME, $field['type']);
        $this->assertSame([2], $field['args']);
    }

    public function testMakeFieldSkipCreatedAtWhenIsTimestamps()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);

        $field = [
            'field' => ColumnName::CREATED_AT,
            'type' => 'timestamp',
            'args' => []
        ];
        $column = Mockery::mock(Column::class);
        $field = $datetimeField->makeField('table', $field, $column, true);
        $this->assertEmpty($field);
    }

    public function testMakeDefaultIsUseCurrent()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);
        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getDefault')
            ->andReturn('CURRENT_TIMESTAMP')
            ->once();

        $result = $datetimeField->makeDefault($column);
        $this->assertSame(ColumnModifier::USE_CURRENT, $result);
    }

    public function testMakeDefaulNormal()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);
        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getDefault')
            ->andReturn('Default');

        $result = $datetimeField->makeDefault($column);
        $this->assertSame("default('Default')", $result);
    }

    public function testIsUseTimestampsTrue()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);

        $columnCreatedAt = Mockery::mock(Column::class);
        $columnCreatedAt->shouldReceive('getName')
            ->andReturn(ColumnName::CREATED_AT);
        $columnCreatedAt->shouldReceive('getNotnull')
            ->andReturnFalse();
        $columnCreatedAt->shouldReceive('getDefault')
            ->andReturnNull();

        $columnUpdatedAt = Mockery::mock(Column::class);
        $columnUpdatedAt->shouldReceive('getName')
            ->andReturn(ColumnName::UPDATED_AT);
        $columnUpdatedAt->shouldReceive('getNotnull')
            ->andReturnFalse();
        $columnUpdatedAt->shouldReceive('getDefault')
            ->andReturnNull();

        $this->assertTrue($datetimeField->isUseTimestamps([$columnUpdatedAt, $columnCreatedAt]));
    }

    public function testIsUseTimestampsWhenDefaultIsNotNull()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);

        $columnCreatedAt = Mockery::mock(Column::class);
        $columnCreatedAt->shouldReceive('getName')
            ->andReturn(ColumnName::CREATED_AT);
        $columnCreatedAt->shouldReceive('getNotnull')
            ->andReturnFalse();
        $columnCreatedAt->shouldReceive('getDefault')
            ->andReturn('Default');

        $columnUpdatedAt = Mockery::mock(Column::class);
        $columnUpdatedAt->shouldReceive('getName')
            ->andReturn(ColumnName::UPDATED_AT);
        $columnUpdatedAt->shouldReceive('getNotnull')
            ->andReturnFalse();
        $columnUpdatedAt->shouldReceive('getDefault')
            ->andReturnNull();

        $this->assertFalse($datetimeField->isUseTimestamps([$columnUpdatedAt, $columnCreatedAt]));
    }

    public function testIsUseTimestampsOnlyHasCreatedAt()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);

        $columnCreatedAt = Mockery::mock(Column::class);
        $columnCreatedAt->shouldReceive('getName')
            ->andReturn(ColumnName::CREATED_AT);
        $columnCreatedAt->shouldReceive('getNotnull')
            ->andReturnFalse();
        $columnCreatedAt->shouldReceive('getDefault')
            ->andReturnNull();

        $columnUpdatedAt = Mockery::mock(Column::class);
        $columnUpdatedAt->shouldReceive('getName')
            ->andReturn('other');
        $columnUpdatedAt->shouldReceive('getNotnull')
            ->andReturnFalse();
        $columnUpdatedAt->shouldReceive('getDefault')
            ->andReturnNull();

        $this->assertFalse($datetimeField->isUseTimestamps([$columnUpdatedAt, $columnCreatedAt]));
    }

    public function testIsUseTimestampsOnlyHasUpdatedAt()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);

        $columnCreatedAt = Mockery::mock(Column::class);
        $columnCreatedAt->shouldReceive('getName')
            ->andReturn('other');
        $columnCreatedAt->shouldReceive('getNotnull')
            ->andReturnFalse();
        $columnCreatedAt->shouldReceive('getDefault')
            ->andReturnNull();

        $columnUpdatedAt = Mockery::mock(Column::class);
        $columnUpdatedAt->shouldReceive('getName')
            ->andReturn(ColumnName::UPDATED_AT);
        $columnUpdatedAt->shouldReceive('getNotnull')
            ->andReturnFalse();
        $columnUpdatedAt->shouldReceive('getDefault')
            ->andReturnNull();

        $this->assertFalse($datetimeField->isUseTimestamps([$columnUpdatedAt, $columnCreatedAt]));
    }
}
