<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Types\DoubleType;
use KitLoong\MigrationsGenerator\Types\EnumType;
use KitLoong\MigrationsGenerator\Types\GeometryCollectionType;
use KitLoong\MigrationsGenerator\Types\GeometryType;
use KitLoong\MigrationsGenerator\Types\IpAddressType;
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

    public function __construct(FieldGenerator $fieldGenerator, IndexGenerator $indexGenerator, ForeignKeyGenerator $foreignKeyGenerator)
    {
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
        Type::addType('double', DoubleType::class);
        Type::addType('enum', EnumType::class);
        Type::addType('geometry', GeometryType::class);
        Type::addType('geometrycollection', GeometryCollectionType::class);
        Type::addType('linestring', LineStringType::class);
        Type::addType('longtext', LongTextType::class);
        Type::addType('mediumint', MediumIntegerType::class);
        Type::addType('mediumtext', MediumTextType::class);
        Type::addType('multilinestring', MultiLineStringType::class);
        Type::addType('multipoint', MultiPointType::class);
        Type::addType('multipolygon', MultiPolygonType::class);
        Type::addType('point', PointType::class);
        Type::addType('polygon', PolygonType::class);
        Type::addType('set', SetType::class);
        Type::addType('timestamp', TimestampType::class);
        Type::addType('uuid', UUIDType::class);
        Type::addType('year', YearType::class);

        // Postgres types
        Type::addType('ipaddress', IpAddressType::class);
        Type::addType('macaddress', MacAddressType::class);

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = DB::connection($database)->getDoctrineConnection();
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('double', 'double');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'enum');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('geometry', 'geometry');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('geometrycollection', 'geometrycollection');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('json', 'json');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('linestring', 'linestring');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('longtext', 'longtext');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('mediumint', 'mediumint');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('mediumtext', 'mediumtext');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('multilinestring', 'multilinestring');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('multipoint', 'multipoint');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('multipolygon', 'multipolygon');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('point', 'point');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('polygon', 'polygon');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'set');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('timestamp', 'timestamp');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('uuid', 'uuid');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('year', 'year');

        // Postgres types
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_text', 'text');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_int4', 'integer');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_numeric', 'float');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('cidr', 'string');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('inet', 'ipaddress');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('macaddr', 'macaddress');

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
}
