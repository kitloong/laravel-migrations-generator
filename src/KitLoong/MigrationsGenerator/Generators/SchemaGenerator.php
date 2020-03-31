<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/28
 * Time: 20:31
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Types\DoubleType;
use KitLoong\MigrationsGenerator\Types\EnumType;
use KitLoong\MigrationsGenerator\Types\GeometryCollectionType;
use KitLoong\MigrationsGenerator\Types\GeometryType;
use KitLoong\MigrationsGenerator\Types\MediumIntegerType;
use KitLoong\MigrationsGenerator\Types\SetType;
use KitLoong\MigrationsGenerator\Types\TimestampType;
use KitLoong\MigrationsGenerator\Types\UUIDType;
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
        /** @var \Doctrine\DBAL\Connection $connection */
        Type::addType('double', DoubleType::class);
        Type::addType('enum', EnumType::class);
        Type::addType('geometry', GeometryType::class);
        Type::addType('geometrycollection', GeometryCollectionType::class);
        Type::addType('mediumint', MediumIntegerType::class);
        Type::addType('set', SetType::class);
        Type::addType('timestamp', TimestampType::class);
        Type::addType('uuid', UUIDType::class);

        $connection = DB::connection($database)->getDoctrineConnection();
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('double', 'double');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'enum');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('geometry', 'geometry');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('geometrycollection', 'geometrycollection');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('json', 'json');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('mediumint', 'mediumint');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'set');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('timestamp', 'timestamp');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('uuid', 'uuid');

        // Postgres types
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_text', 'text');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_int4', 'integer');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_numeric', 'float');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('cidr', 'string');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('inet', 'string');

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
        $indexes = $this->indexGenerator->generate($table);
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
