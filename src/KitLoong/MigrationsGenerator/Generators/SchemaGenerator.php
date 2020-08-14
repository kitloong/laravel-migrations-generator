<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Collection;
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

class SchemaGenerator
{
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

    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected $schema;

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
        $setting = app(MigrationsGeneratorSetting::class);

        foreach (self::$customDoctrineTypes as $doctrineType) {
            $this->registerCustomDoctrineType(...$doctrineType);
        }

        $this->addNewDoctrineType('bit', 'boolean');
        $this->addNewDoctrineType('json', 'json');

        switch ($setting->getPlatform()) {
            case Platform::POSTGRESQL:
                $this->addNewDoctrineType('_text', 'text');
                $this->addNewDoctrineType('_int4', 'integer');
                $this->addNewDoctrineType('_numeric', 'float');
                $this->addNewDoctrineType('cidr', 'string');
                break;
            default:
        }

        $this->schema = $setting->getConnection()->getDoctrineConnection()->getSchemaManager();
    }

    /**
     * @return string[]
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
     */
    public function getIndexes(string $table): array
    {
        return $this->indexGenerator->generate(
            $table,
            $this->schema->listTableIndexes($table),
            app(MigrationsGeneratorSetting::class)->isIgnoreIndexNames()
        );
    }

    public function getFields(string $table, Collection $singleColIndexes): array
    {
        return $this->fieldGenerator->generate(
            $table,
            $this->schema->listTableColumns($table),
            $singleColIndexes
        );
    }

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
     * @throws \Doctrine\DBAL\DBALException
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
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function addNewDoctrineType(string $type, string $name): void
    {
        app(MigrationsGeneratorSetting::class)->getConnection()
            ->getDoctrineConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping($type, $name);
    }
}
