<?php

namespace KitLoong\MigrationsGenerator\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types as DoctrineDBALTypes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\DBAL\Types\CustomType;
use KitLoong\MigrationsGenerator\DBAL\Types\Types;
use KitLoong\MigrationsGenerator\Enum\Driver;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository;

class RegisterColumnType
{
    /**
     * @var \KitLoong\MigrationsGenerator\Repositories\PgSQLRepository
     */
    private $pgSQLRepository;

    /**
     * @var \KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository
     */
    private $sqlSrvRepository;

    public function __construct(PgSQLRepository $pgSQLRepository, SQLSrvRepository $sqlSrvRepository)
    {
        $this->pgSQLRepository  = $pgSQLRepository;
        $this->sqlSrvRepository = $sqlSrvRepository;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function handle(): void
    {
        $this->registerLaravelColumnType();
        $this->registerLaravelCustomColumnType();

        $doctrineTypes = [
            Driver::MYSQL()->getValue()  => [
                'bit'            => DoctrineDBALTypes::BOOLEAN,
                'geomcollection' => ColumnType::GEOMETRY_COLLECTION,
                'mediumint'      => ColumnType::MEDIUM_INTEGER,
                'tinyint'        => ColumnType::TINY_INTEGER,
            ],
            Driver::PGSQL()->getValue()  => [
                '_int4'     => DoctrineDBALTypes::TEXT,
                '_int8'     => DoctrineDBALTypes::TEXT,
                '_numeric'  => DoctrineDBALTypes::FLOAT,
                '_text'     => DoctrineDBALTypes::TEXT,
                'cidr'      => DoctrineDBALTypes::STRING,
                'geography' => ColumnType::GEOMETRY,
                'inet'      => ColumnType::IP_ADDRESS,
                'macaddr'   => ColumnType::MAC_ADDRESS,
                'oid'       => DoctrineDBALTypes::STRING,
            ],
            Driver::SQLITE()->getValue() => [],
            Driver::SQLSRV()->getValue() => [
                'geography'   => ColumnType::GEOMETRY,
                'sysname'     => DoctrineDBALTypes::STRING,
                'hierarchyid' => DoctrineDBALTypes::STRING,
                'money'       => DoctrineDBALTypes::DECIMAL,
                'smallmoney'  => DoctrineDBALTypes::DECIMAL,
                'tinyint'     => ColumnType::TINY_INTEGER,
                'xml'         => DoctrineDBALTypes::TEXT,
            ],
        ];

        // Register DB specific type, and fallback to Laravel column types.
        foreach ($doctrineTypes[DB::getDriverName()] as $dbType => $doctrineType) {
            $this->registerDoctrineTypeMapping($dbType, $doctrineType);
        }
    }

    /**
     * Register additional column types which are supported by the framework.
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function registerLaravelColumnType(): void
    {
        $typesMap = array_flip(Types::ADDITIONAL_TYPES_MAP);

        foreach ($typesMap as $type => $doctrineTypeClassName) {
            // Add a new type by providing a `type` name and a `\Doctrine\DBAL\Types\Type` class name.
            // eg: `$type = double`, `$doctrineTypeClassName = \KitLoong\MigrationsGenerator\DBAL\Types\DoubleType`
            $this->addOrOverrideType($type, $doctrineTypeClassName);

            // Register type mapping so that Doctrine DBAL can recognize the DB column type.
            // eg: `$type = double`
            // Now Doctrine DBAL can recognize `column double NOT NULL` and create a column instance with type `\KitLoong\MigrationsGenerator\DBAL\Types\DoubleType`.
            $this->registerDoctrineTypeMapping($type, $type);
        }
    }

    /**
     * Register additional column types which are not supported by the framework.
     *
     * @note Uses {@see \Doctrine\DBAL\Types\Type::__construct} instead of {@see \Doctrine\DBAL\Types\Type::addType} here as workaround.
     * @throws \Doctrine\DBAL\Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) to suppress `getSQLDeclaration` warning.
     */
    private function registerLaravelCustomColumnType(): void
    {
        foreach ($this->getCustomTypes() as $type) {
            $customType = new class () extends CustomType {
                /**
                 * @var string
                 */
                public $type = '';

                /**
                 * @inheritDoc
                 */
                public function getSQLDeclaration(array $column, AbstractPlatform $platform)
                {
                    return $this->type;
                }

                /**
                 * @inheritDoc
                 */
                public function getName()
                {
                    return $this->type;
                }
            };

            $customType->type = $type;

            if (!Type::hasType($type)) {
                Type::getTypeRegistry()->register($type, $customType);
            }

            $this->registerDoctrineTypeMapping($type, $type);
        }
    }

    /**
     * Get a list of custom type names from DB.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function getCustomTypes(): Collection
    {
        switch (DB::getDriverName()) {
            case Driver::PGSQL():
                return $this->pgSQLRepository->getCustomDataTypes();

            case Driver::SQLSRV():
                return $this->sqlSrvRepository->getCustomDataTypes();

            default:
                return new Collection();
        }
    }

    /**
     * Add or override doctrine type.
     *
     * @param  class-string<\Doctrine\DBAL\Types\Type>  $class  The class name which is extends {@see \Doctrine\DBAL\Types\Type}.
     * @throws \Doctrine\DBAL\Exception
     */
    private function addOrOverrideType(string $type, string $class): void
    {
        if (!Type::hasType($type)) {
            Type::addType($type, $class);
            return;
        }

        Type::overrideType($type, $class);
    }

    /**
     * Registers a doctrine type to be used in conjunction with a column type of this platform.
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function registerDoctrineTypeMapping(string $dbType, string $doctrineType): void
    {
        DB::getDoctrineConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping($dbType, $doctrineType);
    }
}
