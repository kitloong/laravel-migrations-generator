<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\MigrationGeneratorSetting;
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
use Xethron\MigrationsGenerator\Generators\ForeignKeyGenerator;

class SchemaGenerator
{
    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    private $schema;

    /**
     * @var FieldGenerator
     */
    private $fieldGenerator;

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

    public function __construct(
        FieldGenerator $fieldGenerator,
        IndexGenerator $indexGenerator,
        ForeignKeyGenerator $foreignKeyGenerator
    ) {
        $this->fieldGenerator = $fieldGenerator;
        $this->indexGenerator = $indexGenerator;
        $this->foreignKeyGenerator = $foreignKeyGenerator;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function initialize()
    {
        /** @var MigrationGeneratorSetting $setting */
        $setting = app(MigrationGeneratorSetting::class);

        foreach (self::$customDoctrineTypes as $doctrineType) {
            $this->registerCustomDoctrineType(...$doctrineType);
        }

        $connection = $setting->getConnection()->getDoctrineConnection();

        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('json', 'json');

        switch ($setting->getPlatform()) {
            case Platform::POSTGRESQL:
                $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_text', 'text');
                $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_int4', 'integer');
                $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_numeric', 'float');
                $connection->getDatabasePlatform()->registerDoctrineTypeMapping('cidr', 'string');
                break;
            default:
        }

        $this->schema = $connection->getSchemaManager();
    }

    /**
     * @return string[]
     */
    public function getTables(): array
    {
        return $this->schema->listTableNames();
    }

    public function getTable(string $tableName): Table
    {
        return $this->schema->listTableDetails($tableName);
    }

    /**
     * @param  Table  $table
     * @return array|\Illuminate\Support\Collection[]
     * [
     *  'single' => Collection of single column indexes, with column name as key
     *  'multi' => Collection of multi columns indexes
     * ]
     */
    public function getIndexes(Table $table): array
    {
        /** @var MigrationGeneratorSetting $setting */
        $setting = app(MigrationGeneratorSetting::class);

        return $this->indexGenerator->generate($table, $setting->isIgnoreIndexNames());
    }

    public function getFields(Table $table, Collection $singleColIndexes): array
    {
        return $this->fieldGenerator->generate($table, $singleColIndexes);
    }

    public function getForeignKeyConstraints(string $table): array
    {
        /** @var MigrationGeneratorSetting $setting */
        $setting = app(MigrationGeneratorSetting::class);

        return $this->foreignKeyGenerator->generate($table, $this->schema, $setting->isIgnoreForeignKeyNames());
    }

    /**
     * Register custom doctrineType
     * Will override if exists
     *
     * @param $class
     * @param $name
     * @param $type
     * @throws \Doctrine\DBAL\DBALException
     */
    public function registerCustomDoctrineType($class, $name, $type)
    {
        /** @var MigrationGeneratorSetting $setting */
        $setting = app(MigrationGeneratorSetting::class);

        if (!Type::hasType($name)) {
            Type::addType($name, $class);
        } else {
            Type::overrideType($name, $class);
        }

        $setting->getConnection()
            ->getDoctrineConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping($type, $name);
    }
}
