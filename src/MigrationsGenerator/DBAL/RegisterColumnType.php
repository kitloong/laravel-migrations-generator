<?php

namespace MigrationsGenerator\DBAL;

use Doctrine\DBAL\Types\Type;
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
use MigrationsGenerator\DBAL\Types\Types;
use MigrationsGenerator\DBAL\Types\UUIDType;
use MigrationsGenerator\DBAL\Types\YearType;
use MigrationsGenerator\MigrationsGeneratorSetting;

class RegisterColumnType
{
    private $setting;

    public function __construct(MigrationsGeneratorSetting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function handle(): void
    {
        /**
         * The map of supported doctrine mapping types.
         */
        $customTypeMap = [
            // [$name, $className]
            Types::DOUBLE              => DoubleType::class,
            Types::ENUM                => EnumType::class,
            Types::GEOMETRY            => GeometryType::class,
            Types::GEOMETRY_COLLECTION => GeometryCollectionType::class,
            Types::IP_ADDRESS          => IpAddressType::class,
            Types::JSONB               => JsonbType::class,
            Types::LINE_STRING         => LineStringType::class,
            Types::LONG_TEXT           => LongTextType::class,
            Types::MAC_ADDRESS         => MacAddressType::class,
            Types::MEDIUM_INTEGER      => MediumIntegerType::class,
            Types::MEDIUM_TEXT         => MediumTextType::class,
            Types::MULTI_LINE_STRING   => MultiLineStringType::class,
            Types::MULTI_POINT         => MultiPointType::class,
            Types::MULTI_POLYGON       => MultiPolygonType::class,
            Types::POINT               => PointType::class,
            Types::POLYGON             => PolygonType::class,
            Types::SET                 => SetType::class,
            Types::TIMESTAMP           => TimestampType::class,
            Types::TIMESTAMP_TZ        => TimestampTzType::class,
            Types::TIME_TZ             => TimeTzType::class,
            Types::TINY_INTEGER        => TinyIntegerType::class,
            Types::UUID                => UUIDType::class,
            Types::YEAR                => YearType::class,
        ];

        foreach ($customTypeMap as $dbType => $class) {
            $this->registerCustomDoctrineType($dbType, $class);
        }

        $doctrineTypes = [
            Platform::MYSQL      => [
                'bit'            => Types::BOOLEAN,
                'geomcollection' => Types::GEOMETRY_COLLECTION,
                'json'           => Types::JSON,
                'mediumint'      => Types::MEDIUM_INTEGER,
                'tinyint'        => Types::TINY_INTEGER,
            ],
            Platform::POSTGRESQL => [
                '_int4'     => Types::TEXT,
                '_numeric'  => Types::FLOAT,
                '_text'     => Types::TEXT,
                'cidr'      => Types::STRING,
                'geography' => Types::GEOMETRY,
                'inet'      => Types::IP_ADDRESS,
                'macaddr'   => Types::MAC_ADDRESS,
                'oid'       => Types::STRING,
            ],
            Platform::SQLSERVER  => [
                'geography'  => Types::GEOMETRY,
                'money'      => Types::DECIMAL,
                'smallmoney' => Types::DECIMAL,
                'tinyint'    => Types::TINY_INTEGER,
                'xml'        => Types::TEXT,
            ],
        ];

        foreach ($doctrineTypes[$this->setting->getPlatform()] as $dbType => $doctrineType) {
            $this->registerDoctrineTypeMapping($dbType, $doctrineType);
        }
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
        if (!Type::hasType($dbType)) {
            Type::addType($dbType, $class);
        } else {
            Type::overrideType($dbType, $class);
        }

        $this->registerDoctrineTypeMapping($dbType, $dbType);
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
        $this->setting->getConnection()
            ->getDoctrineConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping($dbType, $doctrineType);
    }
}
