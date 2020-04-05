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
use Doctrine\DBAL\Types\Type;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Generators\FieldGenerator;
use KitLoong\MigrationsGenerator\Generators\IndexGenerator;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\Generators\SchemaGenerator;
use KitLoong\MigrationsGenerator\MigrationGeneratorSetting;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\Types\DoubleType;
use KitLoong\MigrationsGenerator\Types\EnumType;
use KitLoong\MigrationsGenerator\Types\GeometryCollectionType;
use KitLoong\MigrationsGenerator\Types\GeometryType;
use KitLoong\MigrationsGenerator\Types\IpAddressType;
use KitLoong\MigrationsGenerator\Types\JsonbType;
use KitLoong\MigrationsGenerator\Types\LineStringType;
use KitLoong\MigrationsGenerator\Types\LongTextType;
use KitLoong\MigrationsGenerator\Types\MacAddressType;
use KitLoong\MigrationsGenerator\Types\MediumIntegerType;
use KitLoong\MigrationsGenerator\Types\MediumTextType;
use KitLoong\MigrationsGenerator\Types\MultiLineStringType;
use KitLoong\MigrationsGenerator\Types\MultiPointType;
use KitLoong\MigrationsGenerator\Types\MultiPolygonType;
use KitLoong\MigrationsGenerator\Types\PointType;
use KitLoong\MigrationsGenerator\Types\PolygonType;
use KitLoong\MigrationsGenerator\Types\SetType;
use KitLoong\MigrationsGenerator\Types\TimestampType;
use KitLoong\MigrationsGenerator\Types\TimestampTzType;
use KitLoong\MigrationsGenerator\Types\TimeTzType;
use KitLoong\MigrationsGenerator\Types\TinyIntegerType;
use KitLoong\MigrationsGenerator\Types\UUIDType;
use KitLoong\MigrationsGenerator\Types\YearType;
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

        $this->mock(MigrationGeneratorSetting::class, function (MockInterface $mock) {
            $this->mockShouldReceivedCustomType($mock);

            $mock->shouldReceive('getPlatform')
                ->andReturn(Platform::POSTGRESQL)
                ->once();
        });

        DB::shouldReceive('connection')
            ->with('database')
            ->andReturn($connection)
            ->once();

        $connection->shouldReceive('getDoctrineConnection')
            ->andReturn($dbalConnection)
            ->once();

        $this->mockShouldReceivedDoctrineType($dbalConnection);

        $schema = Mockery::mock(AbstractSchemaManager::class);

        $dbalConnection->shouldReceive('getSchemaManager')
            ->andReturn($schema)
            ->once();

        $schema->shouldReceive('listTableNames')
            ->andReturn(['table1', 'table2'])
            ->once();

        $table = Mockery::mock(Table::class);

        $schema->shouldReceive('listTableDetails')
            ->with('table1')
            ->andReturn($table)
            ->once();

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
                ->andReturn(['fields'])
                ->once();
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

        $this->assertSame($table, $schemaGenerator->getTable('table1'));

        $indexes = $schemaGenerator->getIndexes($table);

        $this->assertSame(['fields'], $schemaGenerator->getFields($table, $indexes['single']));

        $schemaGenerator->getForeignKeyConstraints('table');
    }

    public function testRegisterCustomDoctrineType()
    {
        $this->mock(MigrationGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getDatabasePlatform->registerDoctrineTypeMapping')
                ->with('inet', 'ipaddress')
                ->twice();
        });

        /** @var SchemaGenerator $schemaGenerator */
        $schemaGenerator = resolve(SchemaGenerator::class);

        $schemaGenerator->registerCustomDoctrineType(IpAddressType::class, 'ipaddress', 'inet');

        $this->assertSame(ColumnType::IP_ADDRESS, Type::getType('ipaddress')->getName());

        // Register same type should not throw type exists exception
        $schemaGenerator->registerCustomDoctrineType(IpAddressType::class, 'ipaddress', 'inet');
    }

    private function mockShouldReceivedCustomType(MockInterface $mock)
    {
        foreach ($this->getTypes() as $type) {
            $mock->shouldReceive('getDatabasePlatform->registerDoctrineTypeMapping')
                ->with($type[1], $type[0])
                ->once();
        }
    }

    private function mockShouldReceivedDoctrineType(MockInterface $mock)
    {
        $types = [
            'bit' => 'boolean',
            'json' => 'json',

            '_text' => 'text',
            '_int4' => 'integer',
            '_numeric' => 'float',
            'cidr' => 'string'
        ];

        foreach ($types as $dbType => $doctrineType) {
            $mock->shouldReceive('getDatabasePlatform->registerDoctrineTypeMapping')
                ->with($dbType, $doctrineType)
                ->once();
        }
    }

    private function getTypes()
    {
        return [
            DoubleType::class => ['double', 'double'],
            EnumType::class => ['enum', 'enum'],
            GeometryType::class => ['geometry', 'geometry'],
            GeometryCollectionType::class => ['geometrycollection', 'geometrycollection'],
            LineStringType::class => ['linestring', 'linestring'],
            LongTextType::class => ['longtext', 'longtext'],
            MediumIntegerType::class => ['mediumint', 'mediumint'],
            MediumTextType::class => ['mediumtext', 'mediumtext'],
            MultiLineStringType::class => ['multilinestring', 'multilinestring'],
            MultiPointType::class => ['multipoint', 'multipoint'],
            MultiPolygonType::class => ['multipolygon', 'multipolygon'],
            PointType::class => ['point', 'point'],
            PolygonType::class => ['polygon', 'polygon'],
            SetType::class => ['set', 'set'],
            TimestampType::class => ['timestamp', 'timestamp'],
            TinyIntegerType::class => ['tinyint', 'tinyint'],
            UUIDType::class => ['uuid', 'uuid'],
            YearType::class => ['year', 'year'],

            // Postgres types
            IpAddressType::class => ['ipaddress', 'inet'],
            JsonbType::class => ['jsonb', 'jsonb'],
            MacAddressType::class => ['macaddress', 'macaddr'],
            TimeTzType::class => ['timetz', 'timetz'],
            TimestampTzType::class => ['timestamptz', 'timestamptz']
        ];
    }
}
