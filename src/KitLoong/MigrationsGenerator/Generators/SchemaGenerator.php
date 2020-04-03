<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\MigrationGeneratorSetting;
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
use KitLoong\MigrationsGenerator\Types\UUIDType;
use KitLoong\MigrationsGenerator\Types\YearType;
use Xethron\MigrationsGenerator\Generators\ForeignKeyGenerator;

class SchemaGenerator
{
    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected $schema;

    /**
     * @var FieldGenerator
     */
    protected $fieldGenerator;

    /**
     * @var ForeignKeyGenerator
     */
    protected $foreignKeyGenerator;

    private $indexGenerator;

    /**
     * @var string
     */
    protected $database;
    /**
     * @var bool
     */
    private $ignoreIndexNames;
    /**
     * @var bool
     */
    private $ignoreForeignKeyNames;

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
     * @param  string  $database
     * @param  bool  $ignoreIndexNames
     * @param  bool  $ignoreForeignKeyNames
     * @throws \Doctrine\DBAL\DBALException
     */
    public function initialize(string $database, bool $ignoreIndexNames, bool $ignoreForeignKeyNames)
    {
        /** @var MigrationGeneratorSetting $setting */
        $setting = resolve(MigrationGeneratorSetting::class);

        $this->registerCustomDoctrineType(DoubleType::class, 'double', 'double');
        $this->registerCustomDoctrineType(EnumType::class, 'enum', 'enum');
        $this->registerCustomDoctrineType(GeometryType::class, 'geometry', 'geometry');
        $this->registerCustomDoctrineType(GeometryCollectionType::class, 'geometrycollection', 'geometrycollection');
        $this->registerCustomDoctrineType(LineStringType::class, 'linestring', 'linestring');
        $this->registerCustomDoctrineType(LongTextType::class, 'longtext', 'longtext');
        $this->registerCustomDoctrineType(MediumIntegerType::class, 'mediumint', 'mediumint');
        $this->registerCustomDoctrineType(MediumTextType::class, 'mediumtext', 'mediumtext');
        $this->registerCustomDoctrineType(MultiLineStringType::class, 'multilinestring', 'multilinestring');
        $this->registerCustomDoctrineType(MultiPointType::class, 'multipoint', 'multipoint');
        $this->registerCustomDoctrineType(MultiPolygonType::class, 'multipolygon', 'multipolygon');
        $this->registerCustomDoctrineType(PointType::class, 'point', 'point');
        $this->registerCustomDoctrineType(PolygonType::class, 'polygon', 'polygon');
        $this->registerCustomDoctrineType(SetType::class, 'set', 'set');
        $this->registerCustomDoctrineType(TimestampType::class, 'timestamp', 'timestamp');
        $this->registerCustomDoctrineType(UUIDType::class, 'uuid', 'uuid');
        $this->registerCustomDoctrineType(YearType::class, 'year', 'year');

        // Postgres types
        $this->registerCustomDoctrineType(IpAddressType::class, 'ipaddress', 'inet');
        $this->registerCustomDoctrineType(JsonbType::class, 'jsonb', 'jsonb');
        $this->registerCustomDoctrineType(MacAddressType::class, 'macaddress', 'macaddr');
        $this->registerCustomDoctrineType(TimeTzType::class, 'timetz', 'timetz');
        $this->registerCustomDoctrineType(TimestampTzType::class, 'timestamptz', 'timestamptz');

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = DB::connection($database)->getDoctrineConnection();

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

        $this->database = $connection->getDatabase();

        $this->schema = $connection->getSchemaManager();

        $this->ignoreIndexNames = $ignoreIndexNames;
        $this->ignoreForeignKeyNames = $ignoreForeignKeyNames;
    }

    /**
     * @return string[]
     */
    public function getTables(): array
    {
        return $this->schema->listTableNames();
    }

    public function getFields(string $tableName): array
    {
        $table = $this->schema->listTableDetails($tableName);
        $indexes = $this->indexGenerator->generate($table, $this->ignoreIndexNames);
        $singleColIndexes = $indexes['single'];
        $multiColIndexes = $indexes['multi'];
        $fields = $this->fieldGenerator->generate($table, $singleColIndexes);
        return array_merge($fields, $multiColIndexes->toArray());
    }

    public function getForeignKeyConstraints(string $table): array
    {
        return $this->foreignKeyGenerator->generate($table, $this->schema, $this->ignoreForeignKeyNames);
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
        $setting = resolve(MigrationGeneratorSetting::class);

        if (!Type::hasType($name)) {
            Type::addType($name, $class);
        } else {
            Type::overrideType($name, $class);
        }

        $setting->getDatabasePlatform()
            ->registerDoctrineTypeMapping($type, $name);
    }
}
