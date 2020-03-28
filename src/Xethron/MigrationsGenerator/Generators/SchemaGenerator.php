<?php namespace Xethron\MigrationsGenerator\Generators;

use Illuminate\Support\Facades\DB;

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
     * @param  string  $database
     * @param  bool  $ignoreIndexNames
     * @param  bool  $ignoreForeignKeyNames
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct($database, $ignoreIndexNames, $ignoreForeignKeyNames)
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = DB::connection($database)->getDoctrineConnection();
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('json', 'text');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('jsonb', 'text');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');

        // Postgres types
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_text', 'text');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_int4', 'integer');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('_numeric', 'float');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('cidr', 'string');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('inet', 'string');

        $this->database = $connection->getDatabase();

        $this->schema = $connection->getSchemaManager();
        $this->fieldGenerator = new FieldGenerator();
        $this->foreignKeyGenerator = new ForeignKeyGenerator();

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

    public function getFields($table): array
    {
        return $this->fieldGenerator->generate($table, $this->schema, $this->database, $this->ignoreIndexNames);
    }

    public function getForeignKeyConstraints($table): array
    {
        return $this->foreignKeyGenerator->generate($table, $this->schema, $this->ignoreForeignKeyNames);
    }
}
