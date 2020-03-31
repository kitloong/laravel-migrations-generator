<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 19:51
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use KitLoong\MigrationsGenerator\Generators\IndexGenerator;
use KitLoong\MigrationsGenerator\MigrationMethod\IndexType;
use Mockery;
use Orchestra\Testbench\TestCase;

class IndexGeneratorTest extends TestCase
{
    public function testGenerateIsPrimary()
    {
        /** @var IndexGenerator $indexGenerator */
        $indexGenerator = resolve(IndexGenerator::class);

        $index = Mockery::mock(Index::class);
        $index->shouldReceive('getColumns')
            ->andReturn(['col1']);
        $index->shouldReceive('isPrimary')
            ->andReturnTrue();
        $index->shouldReceive('getName')
            ->andReturn('name');

        $table = Mockery::mock(Table::class);
        $table->shouldReceive('getName')
            ->andReturn('table');
        $table->shouldReceive('getIndexes')
            ->andReturn([$index]);

        $result = $indexGenerator->generate($table, false);
        $this->assertSame([
            'col1' => [
                'field' => ['col1'],
                'type' => IndexType::PRIMARY,
                'args' => ["'name'"]
            ]
        ], $result['single']->toArray());
        $this->assertEmpty($result['multi']);
    }

    public function testGenerateIsUnique()
    {
        /** @var IndexGenerator $indexGenerator */
        $indexGenerator = resolve(IndexGenerator::class);

        $index = Mockery::mock(Index::class);
        $index->shouldReceive('getColumns')
            ->andReturn(['col1']);
        $index->shouldReceive('isPrimary')
            ->andReturnFalse();
        $index->shouldReceive('isUnique')
            ->andReturnTrue();
        $index->shouldReceive('getName')
            ->andReturn('name');

        $table = Mockery::mock(Table::class);
        $table->shouldReceive('getName')
            ->andReturn('table');
        $table->shouldReceive('getIndexes')
            ->andReturn([$index]);

        $result = $indexGenerator->generate($table, false);
        $this->assertSame([
            'col1' => [
                'field' => ['col1'],
                'type' => IndexType::UNIQUE,
                'args' => ["'name'"]
            ]
        ], $result['single']->toArray());
    }

    public function testGenerateIsSpatialIndex()
    {
        /** @var IndexGenerator $indexGenerator */
        $indexGenerator = resolve(IndexGenerator::class);

        $index = Mockery::mock(Index::class);
        $index->shouldReceive('getColumns')
            ->andReturn(['col1']);
        $index->shouldReceive('isPrimary')
            ->andReturnFalse();
        $index->shouldReceive('isUnique')
            ->andReturnFalse();
        $index->shouldReceive('getFlags')
            ->andReturn(['spatial']);
        $index->shouldReceive('getName')
            ->andReturn('name');

        $table = Mockery::mock(Table::class);
        $table->shouldReceive('getName')
            ->andReturn('table');
        $table->shouldReceive('getIndexes')
            ->andReturn([$index]);

        $result = $indexGenerator->generate($table, false);
        $this->assertSame([
            'col1' => [
                'field' => ['col1'],
                'type' => IndexType::SPATIAL_INDEX,
                'args' => ["'name'"]
            ]
        ], $result['single']->toArray());
    }

    public function testGenerateIsIndex()
    {
        /** @var IndexGenerator $indexGenerator */
        $indexGenerator = resolve(IndexGenerator::class);

        $index = Mockery::mock(Index::class);
        $index->shouldReceive('getColumns')
            ->andReturn(['col1']);
        $index->shouldReceive('isPrimary')
            ->andReturnFalse();
        $index->shouldReceive('isUnique')
            ->andReturnFalse();
        $index->shouldReceive('getFlags')
            ->andReturn([]);
        $index->shouldReceive('getName')
            ->andReturn('name');

        $table = Mockery::mock(Table::class);
        $table->shouldReceive('getName')
            ->andReturn('table');
        $table->shouldReceive('getIndexes')
            ->andReturn([$index]);

        $result = $indexGenerator->generate($table, false);
        $this->assertSame([
            'col1' => [
                'field' => ['col1'],
                'type' => IndexType::INDEX,
                'args' => ["'name'"]
            ]
        ], $result['single']->toArray());
    }

    public function testGenerateIsMultiColumn()
    {
        /** @var IndexGenerator $indexGenerator */
        $indexGenerator = resolve(IndexGenerator::class);

        $index = Mockery::mock(Index::class);
        $index->shouldReceive('getColumns')
            ->andReturn(['col1', 'col2']);
        $index->shouldReceive('isPrimary')
            ->andReturnFalse();
        $index->shouldReceive('isUnique')
            ->andReturnFalse();
        $index->shouldReceive('getFlags')
            ->andReturn([]);
        $index->shouldReceive('getName')
            ->andReturn('name');

        $table = Mockery::mock(Table::class);
        $table->shouldReceive('getName')
            ->andReturn('table');
        $table->shouldReceive('getIndexes')
            ->andReturn([$index]);

        $result = $indexGenerator->generate($table, false);
        $this->assertSame([
            0 => [
                'field' => ['col1', 'col2'],
                'type' => IndexType::INDEX,
                'args' => ["'name'"]
            ]
        ], $result['multi']->toArray());
        $this->assertEmpty($result['single']);
    }

    public function testGenerateUseLaravelDefaultName()
    {
        /** @var IndexGenerator $indexGenerator */
        $indexGenerator = resolve(IndexGenerator::class);

        $index = Mockery::mock(Index::class);
        $index->shouldReceive('getColumns')
            ->andReturn(['col1', 'col2']);
        $index->shouldReceive('isPrimary')
            ->andReturnFalse();
        $index->shouldReceive('isUnique')
            ->andReturnFalse();
        $index->shouldReceive('getFlags')
            ->andReturn([]);
        $index->shouldReceive('getName')
            ->andReturn('table_col1_col2_index');

        $table = Mockery::mock(Table::class);
        $table->shouldReceive('getName')
            ->andReturn('table');
        $table->shouldReceive('getIndexes')
            ->andReturn([$index]);

        $result = $indexGenerator->generate($table, false);
        $this->assertSame([
            0 => [
                'field' => ['col1', 'col2'],
                'type' => IndexType::INDEX,
                'args' => []
            ]
        ], $result['multi']->toArray());
        $this->assertEmpty($result['single']);
    }

    public function testGenerateIgnoreIndexName()
    {
        /** @var IndexGenerator $indexGenerator */
        $indexGenerator = resolve(IndexGenerator::class);

        $index = Mockery::mock(Index::class);
        $index->shouldReceive('getColumns')
            ->andReturn(['col1', 'col2']);
        $index->shouldReceive('isPrimary')
            ->andReturnFalse();
        $index->shouldReceive('isUnique')
            ->andReturnFalse();
        $index->shouldReceive('getFlags')
            ->andReturn([]);
        $index->shouldReceive('getName')
            ->andReturn('name');

        $table = Mockery::mock(Table::class);
        $table->shouldReceive('getName')
            ->andReturn('table');
        $table->shouldReceive('getIndexes')
            ->andReturn([$index]);

        $result = $indexGenerator->generate($table, true);
        $this->assertSame([
            0 => [
                'field' => ['col1', 'col2'],
                'type' => IndexType::INDEX,
                'args' => []
            ]
        ], $result['multi']->toArray());
        $this->assertEmpty($result['single']);
    }
}
