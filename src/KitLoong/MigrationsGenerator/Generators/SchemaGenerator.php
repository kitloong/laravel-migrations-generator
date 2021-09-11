<?php

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\DBAL\Types\DoubleType;
use KitLoong\MigrationsGenerator\DBAL\Types\EnumType;
use KitLoong\MigrationsGenerator\DBAL\Types\GeographyType;
use KitLoong\MigrationsGenerator\DBAL\Types\GeomCollectionType;
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
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class SchemaGenerator
{
    /**
     * @var ForeignKeyGenerator
     */
    private $foreignKeyGenerator;

    private $indexGenerator;

    /**
     * Custom doctrine type
     * ['class', 'name', 'type']
     * @see registerCustomDoctrineType()
     *
     * @var array
     */
    private static $customDoctrineTypes = [
        [DoubleType::class, 'double', 'double'],
        [EnumType::class, 'enum', 'enum'],
        [GeometryType::class, 'geometry', 'geometry'],
        [GeomCollectionType::class, 'geomcollection', 'geomcollection'],
        [GeometryCollectionType::class, 'geometrycollection', 'geometrycollection'],
        [LineStringType::class, 'linestring', 'linestring'],
        [LongTextType::class, 'longtext', 'longtext'],
        [MediumIntegerType::class, 'mediumint', 'mediumint'],
        [MediumTextType::class, 'mediumtext', 'mediumtext'],
        [MultiLineStringType::class, 'multilinestring', 'multilinestring'],
        [MultiPointType::class, 'multipoint', 'multipoint'],
        [MultiPolygonType::class, 'multipolygon', 'multipolygon'],
        [PointType::class, 'point', 'point'],
        [PolygonType::class, 'polygon', 'polygon'],
        [SetType::class, 'set', 'set'],
        [TimestampType::class, 'timestamp', 'timestamp'],
        [TinyIntegerType::class, 'tinyint', 'tinyint'],
        [UUIDType::class, 'uuid', 'uuid'],
        [YearType::class, 'year', 'year'],

        // Postgres types
        [GeographyType::class, 'geography', 'geography'],
        [IpAddressType::class, 'ipaddress', 'inet'],
        [JsonbType::class, 'jsonb', 'jsonb'],
        [MacAddressType::class, 'macaddress', 'macaddr'],
        [TimeTzType::class, 'timetz', 'timetz'],
        [TimestampTzType::class, 'timestamptz', 'timestamptz'],
    ];

    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected $schema;

    public function __construct(
        IndexGenerator $indexGenerator,
        ForeignKeyGenerator $foreignKeyGenerator
    ) {
        $this->indexGenerator = $indexGenerator;
        $this->foreignKeyGenerator = $foreignKeyGenerator;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function initialize()
    {
        $setting = app(MigrationsGeneratorSetting::class);

        foreach (self::$customDoctrineTypes as $doctrineType) {
            $this->registerCustomDoctrineType(...$doctrineType);
        }

        $this->addNewDoctrineType('bit', 'boolean');
        $this->addNewDoctrineType('json', 'json');

        switch ($setting->getPlatform()) {
            case Platform::POSTGRESQL:
                $this->addNewDoctrineType('_text', 'text');
                $this->addNewDoctrineType('_int4', 'text');
                $this->addNewDoctrineType('_numeric', 'float');
                $this->addNewDoctrineType('cidr', 'string');
                $this->addNewDoctrineType('oid', 'string');
                break;
            default:
        }

        $this->schema = $setting->getConnection()->getDoctrineConnection()->getSchemaManager();
    }

    /**
     * @return string[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTables(): array
    {
        return $this->schema->listTableNames();
    }

    /**
     * @param  string  $table
     * @return array|\Illuminate\Support\Collection[]
     * [
     *  'single' => Collection of single column indexes, with column name as key
     *  'multi' => Collection of multi columns indexes
     * ]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getIndexes(string $table): array
    {
        return $this->indexGenerator->_generate(
            $table,
            $this->schema->listTableIndexes($table),
            app(MigrationsGeneratorSetting::class)->isIgnoreIndexNames()
        );
    }

    /**
     * @param  string  $table
     * @param  \Illuminate\Support\Collection  $singleColIndexes
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getFields(string $table, Collection $singleColIndexes): array
    {
        return $this->fieldGenerator->generate(
            $table,
            $this->schema->listTableColumns($table),
            $singleColIndexes
        );
    }

    /**
     * @param  string  $table
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getForeignKeyConstraints(string $table): array
    {
        return $this->foreignKeyGenerator->generate(
            $table,
            $this->schema->listTableForeignKeys($table),
            app(MigrationsGeneratorSetting::class)->isIgnoreForeignKeyNames()
        );
    }

    /**
     * Register custom doctrineType
     * Will override if exists
     *
     * @param  string  $class
     * @param  string  $name
     * @param  string  $type
     * @throws \Doctrine\DBAL\Exception
     */
    protected function registerCustomDoctrineType(string $class, string $name, string $type): void
    {
        if (!Type::hasType($name)) {
            Type::addType($name, $class);
        } else {
            Type::overrideType($name, $class);
        }

        $this->addNewDoctrineType($type, $name);
    }

    /**
     * @param  string  $type
     * @param  string  $name
     * @throws \Doctrine\DBAL\Exception
     */
    protected function addNewDoctrineType(string $type, string $name): void
    {
        app(MigrationsGeneratorSetting::class)->getConnection()
            ->getDoctrineConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping($type, $name);
    }
}
