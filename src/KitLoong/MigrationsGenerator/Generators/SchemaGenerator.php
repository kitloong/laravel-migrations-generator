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
        [UUIDType::class, 'uuid', 'uuid'],
        [YearType::class, 'year', 'year'],

        // Postgres types
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
     * @param  string  $database
     * @param  bool  $ignoreIndexNames
     * @param  bool  $ignoreForeignKeyNames
     * @throws \Doctrine\DBAL\DBALException
     */
    public function initialize(string $database, bool $ignoreIndexNames, bool $ignoreForeignKeyNames)
    {
        foreach (self::$customDoctrineTypes as $doctrineType) {
            $this->registerCustomDoctrineType(...$doctrineType);
        }

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = DB::connection($database)->getDoctrineConnection();

        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('json', 'json');

        /** @var MigrationGeneratorSetting $setting */
        $setting = resolve(MigrationGeneratorSetting::class);

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
