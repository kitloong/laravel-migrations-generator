<?php

namespace MigrationsGenerator\DBAL;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\View as DBALView;
use Doctrine\DBAL\Types\Type;
use MigrationsGenerator\DBAL\Mapper\ViewMapper;
use MigrationsGenerator\DBAL\Support\FilterTables;
use MigrationsGenerator\DBAL\Support\FilterViews;
use MigrationsGenerator\DBAL\Types\DBALTypes;
use MigrationsGenerator\DBAL\Types\DoubleType;
use MigrationsGenerator\DBAL\Types\EnumType;
use MigrationsGenerator\DBAL\Types\GeometryCollectionType;
use MigrationsGenerator\DBAL\Types\GeometryType;
use MigrationsGenerator\DBAL\Types\IpAddressType;
use MigrationsGenerator\DBAL\Types\JsonbType;
use MigrationsGenerator\DBAL\Types\LineStringType;
use MigrationsGenerator\DBAL\Types\LongTextType;
use MigrationsGenerator\DBAL\Types\MacAddressType;
use MigrationsGenerator\DBAL\Types\MediumIntegerType;
use MigrationsGenerator\DBAL\Types\MediumTextType;
use MigrationsGenerator\DBAL\Types\MultiLineStringType;
use MigrationsGenerator\DBAL\Types\MultiPointType;
use MigrationsGenerator\DBAL\Types\MultiPolygonType;
use MigrationsGenerator\DBAL\Types\PointType;
use MigrationsGenerator\DBAL\Types\PolygonType;
use MigrationsGenerator\DBAL\Types\SetType;
use MigrationsGenerator\DBAL\Types\TimestampType;
use MigrationsGenerator\DBAL\Types\TimestampTzType;
use MigrationsGenerator\DBAL\Types\TimeTzType;
use MigrationsGenerator\DBAL\Types\TinyIntegerType;
use MigrationsGenerator\DBAL\Types\UUIDType;
use MigrationsGenerator\DBAL\Types\YearType;
use MigrationsGenerator\MigrationsGeneratorSetting;
use MigrationsGenerator\Models\View;

class Schema
{
    use FilterTables;
    use FilterViews;

    private $schema;

    public function __construct()
    {
        $this->schema = app(MigrationsGeneratorSetting::class)->getSchema();
    }

    /**
     * Register custom column type into doctrine dbal.
     *
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
     * Get a list of table names.
     *
     * @return string[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTableNames(): array
    {
        return collect($this->schema->listTables())
            ->filter(call_user_func([$this, 'filterTableCallback']))
            ->map(function (Table $table) {
                return $table->getName();
            })->toArray();
    }

    /**
     * Get single table detail.
     *
     * @param  string  $table  Table name.
     * @return \Doctrine\DBAL\Schema\Table
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTable(string $table): Table
    {
        return $this->schema->listTableDetails($table);
    }

    /**
     * Get a list of table indexes.
     *
     * @param  string  $table  Table name.
     * @return \Doctrine\DBAL\Schema\Index[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getIndexes(string $table): array
    {
        return $this->schema->listTableIndexes($table);
    }

    /**
     * Get a list of table columns.
     *
     * @param  string  $table  Table name.
     * @return \Doctrine\DBAL\Schema\Column[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getColumns(string $table): array
    {
        return $this->schema->listTableColumns($table);
    }

    /**
     * Get a list of table foreign keys.
     *
     * @param  string  $table  Table name.
     * @return \Doctrine\DBAL\Schema\ForeignKeyConstraint[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getForeignKeys(string $table): array
    {
        return $this->schema->listTableForeignKeys($table);
    }

    /**
     * Get a list of view names.
     *
     * @return string[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getViewNames(): array
    {
        return collect($this->getViews())->map(function (View $view) {
            return $view->getName();
        })->toArray();
    }

    /**
     * Get a list of views.
     *
     * @return \MigrationsGenerator\Models\View[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getViews(): array
    {
        return collect($this->schema->listViews())
            ->filter(call_user_func([$this, 'filterViewCallback']))
            ->map(function (DBALView $view) {
                return ViewMapper::toModel($view);
            })->toArray();
    }

    /**
     * Register custom doctrine type, override if exists.
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
