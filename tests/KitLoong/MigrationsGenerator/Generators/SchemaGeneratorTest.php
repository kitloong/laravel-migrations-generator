<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 21:56
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Generators\FieldGenerator;
use KitLoong\MigrationsGenerator\Generators\IndexGenerator;
use KitLoong\MigrationsGenerator\Generators\SchemaGenerator;
use Mockery;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;
use Xethron\MigrationsGenerator\Generators\ForeignKeyGenerator;

class SchemaGeneratorTest extends TestCase
{
    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testInitialize()
    {
        $dbalConnection = Mockery::mock(Connection::class);
        $connection = Mockery::mock(ConnectionInterface::class);

        DB::shouldReceive('connection')
            ->with('database')
            ->andReturn($connection);

        $connection->shouldReceive('getDoctrineConnection')
            ->andReturn($dbalConnection);

        $dbalConnection->shouldReceive('getDatabasePlatform->registerDoctrineTypeMapping');
        $dbalConnection->shouldReceive('getDatabase')
            ->andReturn('database');

        $schema = Mockery::mock(AbstractSchemaManager::class);

        $dbalConnection->shouldReceive('getSchemaManager')
            ->andReturn($schema);

        $schema->shouldReceive('listTableNames')
            ->andReturn(['table1', 'table2']);

        $table = Mockery::mock(Table::class);

        $schema->shouldReceive('listTableDetails')
            ->with('table1')
            ->andReturn($table);

        $singleColumnIndex = collect(['singleColumnIndex']);
        $multiColumnIndex = collect(['multiColumnIndex']);
        $this->mock(
            IndexGenerator::class,
            function (MockInterface $mock) use ($table, $singleColumnIndex, $multiColumnIndex) {
                $mock->shouldReceive('generate')
                    ->with($table, false)
                    ->andReturn([
                        'single' => $singleColumnIndex,
                        'multi' => $multiColumnIndex,
                    ]);
            }
        );

        $this->mock(FieldGenerator::class, function (MockInterface $mock) use ($table, $singleColumnIndex) {
            $mock->shouldReceive('generate')
                ->with($table, $singleColumnIndex)
                ->andReturn(['fields']);
        });

        $this->mock(ForeignKeyGenerator::class, function (MockInterface $mock) use ($schema) {
            $mock->shouldReceive('generate')
                ->with('table', $schema, false);
        });

        /** @var SchemaGenerator $schemaGenerator */
        $schemaGenerator = resolve(SchemaGenerator::class);

        $schemaGenerator->initialize('database', false, false);

        $tables = $schemaGenerator->getTables();
        $this->assertSame(['table1', 'table2'], $tables);

        $fields = $schemaGenerator->getFields('table1');
        $this->assertSame(['fields', 'multiColumnIndex'], $fields);

        $schemaGenerator->getForeignKeyConstraints('table');
    }
}
