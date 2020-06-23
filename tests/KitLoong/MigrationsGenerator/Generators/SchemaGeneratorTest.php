<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Types\Type;
use KitLoong\MigrationsGenerator\Generators\FieldGenerator;
use KitLoong\MigrationsGenerator\Generators\ForeignKeyGenerator;
use KitLoong\MigrationsGenerator\Generators\IndexGenerator;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\Generators\SchemaGenerator;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use KitLoong\MigrationsGenerator\Types\DoubleType;
use KitLoong\MigrationsGenerator\Types\EnumType;
use KitLoong\MigrationsGenerator\Types\GeographyType;
use KitLoong\MigrationsGenerator\Types\GeomCollectionType;
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

class SchemaGeneratorTest extends TestCase
{
    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testInitialize()
    {
        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $this->mockShouldReceivedCustomType($mock);

            $mock->shouldReceive('getPlatform')
                ->andReturn(Platform::POSTGRESQL)
                ->once();

            $this->mockShouldReceivedDoctrineType($mock);

            $mock->shouldReceive('getConnection->getDoctrineConnection->getSchemaManager')
                ->andReturn(Mockery::mock(AbstractSchemaManager::class))
                ->once();
        });

        $schemaGenerator = resolve(SchemaGenerator::class);

        $schemaGenerator->initialize();
    }

    public function testGetTables()
    {
        $schemaGenerator = resolve(SubSchemaGenerator::class);

        $schemaGenerator->mockSchema()
            ->shouldReceive('listTableNames')
            ->andReturn(['result'])
            ->once();

        $this->assertSame(['result'], $schemaGenerator->getTables());
    }

    public function testGetIndexes()
    {
        $mockIndexes = [Mockery::mock(Index::class)];

        $this->mock(IndexGenerator::class, function (MockInterface $mock) use ($mockIndexes) {
            $mock->shouldReceive('generate')
                ->with('table', $mockIndexes, true)
                ->andReturn(['result'])
                ->once();
        });

        $schemaGenerator = resolve(SubSchemaGenerator::class);

        $schemaGenerator->mockSchema()
            ->shouldReceive('listTableIndexes')
            ->with('table')
            ->andReturn($mockIndexes)
            ->once();

        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('isIgnoreIndexNames')
                ->andReturnTrue()
                ->once();
        });

        $this->assertSame(
            ['result'],
            $schemaGenerator->getIndexes('table')
        );
    }

    public function testGetFields()
    {
        $mockColumns = [Mockery::mock(Column::class)];

        $collection = collect();
        $this->mock(FieldGenerator::class, function (MockInterface $mock) use ($mockColumns, $collection) {
            $mock->shouldReceive('generate')
                ->with('table', $mockColumns, $collection)
                ->andReturn(['result'])
                ->once();
        });

        $schemaGenerator = resolve(SubSchemaGenerator::class);

        $schemaGenerator->mockSchema()
            ->shouldReceive('listTableColumns')
            ->with('table')
            ->andReturn($mockColumns)
            ->once();

        $this->assertSame(
            ['result'],
            $schemaGenerator->getFields('table', $collection)
        );
    }

    public function testGetForeignKeyConstraints()
    {
        $mockForeignKeys = [Mockery::mock(ForeignKeyConstraint::class)];

        $this->mock(ForeignKeyGenerator::class, function (MockInterface $mock) use ($mockForeignKeys) {
            $mock->shouldReceive('generate')
                ->with('table', $mockForeignKeys, true)
                ->andReturn(['result'])
                ->once();
        });

        $schemaGenerator = resolve(SubSchemaGenerator::class);

        $schemaGenerator->mockSchema()
            ->shouldReceive('listTableForeignKeys')
            ->with('table')
            ->andReturn($mockForeignKeys)
            ->once();

        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('isIgnoreForeignKeyNames')
                ->andReturnTrue()
                ->once();
        });

        $this->assertSame(
            ['result'],
            $schemaGenerator->getForeignKeyConstraints('table')
        );
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testRegisterCustomDoctrineType()
    {
        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getConnection->getDoctrineConnection->getDatabasePlatform->registerDoctrineTypeMapping')
                ->with('inet', 'ipaddress')
                ->twice();
        });

        $schemaGenerator = resolve(SubSchemaGenerator::class);

        $schemaGenerator->registerCustomDoctrineType(IpAddressType::class, 'ipaddress', 'inet');

        $this->assertSame(ColumnType::IP_ADDRESS, Type::getType('ipaddress')->getName());

        // Register same type should not throw type exists exception
        $schemaGenerator->registerCustomDoctrineType(IpAddressType::class, 'ipaddress', 'inet');
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testAddNewDoctrineType()
    {
        $this->mock(MigrationsGeneratorSetting::class, function (MockInterface $mock) {
            $mock->shouldReceive('getConnection->getDoctrineConnection->getDatabasePlatform->registerDoctrineTypeMapping')
                ->with('inet', 'ipaddress')
                ->once();
        });

        resolve(SubSchemaGenerator::class)->addNewDoctrineType('inet', 'ipaddress');
    }

    private function mockShouldReceivedCustomType(MockInterface $mock)
    {
        foreach ($this->getTypes() as $type) {
            $mock->shouldReceive('getConnection->getDoctrineConnection->getDatabasePlatform->registerDoctrineTypeMapping')
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
            $mock->shouldReceive('getConnection->getDoctrineConnection->getDatabasePlatform->registerDoctrineTypeMapping')
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
            GeomCollectionType::class => ['geomcollection', 'geomcollection'],
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
            GeographyType::class => ['geography', 'geography'],
            IpAddressType::class => ['ipaddress', 'inet'],
            JsonbType::class => ['jsonb', 'jsonb'],
            MacAddressType::class => ['macaddress', 'macaddr'],
            TimeTzType::class => ['timetz', 'timetz'],
            TimestampTzType::class => ['timestamptz', 'timestamptz']
        ];
    }
}


// phpcs:ignore
class SubSchemaGenerator extends SchemaGenerator
{
    public function registerCustomDoctrineType(string $class, string $name, string $type): void
    {
        parent::registerCustomDoctrineType($class, $name, $type);
    }

    public function addNewDoctrineType(string $type, string $name): void
    {
        parent::addNewDoctrineType($type, $name);
    }

    /**
     * @return AbstractSchemaManager|Mockery\LegacyMockInterface|MockInterface
     */
    public function mockSchema()
    {
        return $this->schema = Mockery::mock(AbstractSchemaManager::class);
    }
}
