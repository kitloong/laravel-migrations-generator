<?php

namespace KitLoong\MigrationsGenerator\DBAL;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use KitLoong\MigrationsGenerator\DBAL\Types\DBALTypes;
use KitLoong\MigrationsGenerator\DBAL\Types\DoubleType;
use KitLoong\MigrationsGenerator\DBAL\Types\EnumType;
use KitLoong\MigrationsGenerator\DBAL\Types\GeometryCollectionType;
use KitLoong\MigrationsGenerator\DBAL\Types\GeometryType;
use KitLoong\MigrationsGenerator\DBAL\Types\IpAddressType;
use KitLoong\MigrationsGenerator\DBAL\Types\JsonbType;
use KitLoong\MigrationsGenerator\DBAL\Types\LineStringType;
use KitLoong\MigrationsGenerator\DBAL\Types\LongTextType;
use KitLoong\MigrationsGenerator\DBAL\Types\MacAddressType;
use KitLoong\MigrationsGenerator\DBAL\Types\MediumIntegerType;
use KitLoong\MigrationsGenerator\DBAL\Types\MediumTextType;
use KitLoong\MigrationsGenerator\DBAL\Types\MultiLineStringType;
use KitLoong\MigrationsGenerator\DBAL\Types\MultiPointType;
use KitLoong\MigrationsGenerator\DBAL\Types\MultiPolygonType;
use KitLoong\MigrationsGenerator\DBAL\Types\PointType;
use KitLoong\MigrationsGenerator\DBAL\Types\PolygonType;
use KitLoong\MigrationsGenerator\DBAL\Types\SetType;
use KitLoong\MigrationsGenerator\DBAL\Types\TimestampType;
use KitLoong\MigrationsGenerator\DBAL\Types\TimestampTzType;
use KitLoong\MigrationsGenerator\DBAL\Types\TimeTzType;
use KitLoong\MigrationsGenerator\DBAL\Types\TinyIntegerType;
use KitLoong\MigrationsGenerator\DBAL\Types\UUIDType;
use KitLoong\MigrationsGenerator\DBAL\Types\YearType;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class Schema
{
    private $schema;

    public function __construct()
    {
        $this->schema = app(MigrationsGeneratorSetting::class)->getConnection()
            ->getDoctrineConnection()
            ->getSchemaManager();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function initialize(): void
    {
        $customTypes = [
            // ['{dbType}', '{customTypeClass}']
            ['double', DoubleType::class],
            ['enum', EnumType::class],
            ['geometry', GeometryType::class],
            ['geometrycollection', GeometryCollectionType::class],
            ['linestring', LineStringType::class],
            ['longtext', LongTextType::class],
            ['mediumint', MediumIntegerType::class],
            ['mediumtext', MediumTextType::class],
            ['multilinestring', MultiLineStringType::class],
            ['multipoint', MultiPointType::class],
            ['multipolygon', MultiPolygonType::class],
            ['point', PointType::class],
            ['polygon', PolygonType::class],
            ['set', SetType::class],
            ['timestamp', TimestampType::class],
            ['tinyint', TinyIntegerType::class],
            ['uuid', UUIDType::class],
            ['year', YearType::class],

            // Postgres types
            ['inet', IpAddressType::class],
            ['jsonb', JsonbType::class],
            ['macaddr', MacAddressType::class],
            ['timetz', TimeTzType::class],
            ['timestamptz', TimestampTzType::class],
        ];
        foreach ($customTypes as $type) {
            $this->registerCustomDoctrineType($type[0], $type[1]);
        }

        $this->registerDoctrineTypeMapping('bit', DBALTypes::BOOLEAN);
        $this->registerDoctrineTypeMapping('json', DBALTypes::JSON);

        $this->registerDoctrineTypeMapping('geomcollection', DBALTypes::GEOMETRY_COLLECTION);
        $this->registerDoctrineTypeMapping('geography', DBALTypes::GEOMETRY);

        switch (app(MigrationsGeneratorSetting::class)->getPlatform()) {
            case Platform::POSTGRESQL:
                $this->registerDoctrineTypeMapping('_text', DBALTypes::TEXT);
                $this->registerDoctrineTypeMapping('_int4', DBALTypes::TEXT);
                $this->registerDoctrineTypeMapping('_numeric', DBALTypes::FLOAT);
                $this->registerDoctrineTypeMapping('cidr', DBALTypes::STRING);
                $this->registerDoctrineTypeMapping('oid', DBALTypes::STRING);
                break;
            default:
        }
    }

    /**
     * @return string[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTableNames(): array
    {
        return $this->schema->listTableNames();
    }

    /**
     * @param  string  $table
     * @return \Doctrine\DBAL\Schema\Table
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTable(string $table): Table
    {
        return $this->schema->listTableDetails($table);
    }

    /**
     * @param  string  $table
     * @return \Doctrine\DBAL\Schema\Index[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getIndexes(string $table): array
    {
        return $this->schema->listTableIndexes($table);
    }

    /**
     * @param  string  $table
     * @return \Doctrine\DBAL\Schema\Column[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getColumns(string $table): array
    {
        return $this->schema->listTableColumns($table);
    }

    /**
     * @param  string  $table
     * @return \Doctrine\DBAL\Schema\ForeignKeyConstraint[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getForeignKeys(string $table): array
    {
        return $this->schema->listTableForeignKeys($table);
    }

    /**
     * Register custom doctrineType, override if exists.
     *
     * @param  string  $dbType
     * @param  string  $class  The class name of the custom type.
     * @throws \Doctrine\DBAL\Exception
     */
    protected function registerCustomDoctrineType(string $dbType, string $class): void
    {
        $doctrineType = (new $class())->getName();
        if (!Type::hasType($doctrineType)) {
            Type::addType($doctrineType, $class);
        } else {
            Type::overrideType($doctrineType, $class);
        }

        $this->registerDoctrineTypeMapping($dbType, $doctrineType);
    }

    /**
     * Registers a doctrine type to be used in conjunction with a column type of this platform.
     *
     * @param  string  $dbType
     * @param  string  $doctrineType
     * @throws \Doctrine\DBAL\Exception
     */
    protected function registerDoctrineTypeMapping(string $dbType, string $doctrineType): void
    {
        app(MigrationsGeneratorSetting::class)->getConnection()
            ->getDoctrineConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping($dbType, $doctrineType);
    }
}
