<?php

namespace KitLoong\MigrationsGenerator;

use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use KitLoong\MigrationsGenerator\DBAL\MySQLSchema as DBALMySQLSchema;
use KitLoong\MigrationsGenerator\DBAL\PgSQLSchema as DBALPgSQLSchema;
use KitLoong\MigrationsGenerator\DBAL\SQLiteSchema as DBALSQLiteSchema;
use KitLoong\MigrationsGenerator\DBAL\SQLSrvSchema as DBALSQLSrvSchema;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Migration\Generator\Columns\BooleanColumn;
use KitLoong\MigrationsGenerator\Migration\Generator\Columns\DatetimeColumn;
use KitLoong\MigrationsGenerator\Migration\Generator\Columns\DecimalColumn;
use KitLoong\MigrationsGenerator\Migration\Generator\Columns\DoubleColumn;
use KitLoong\MigrationsGenerator\Migration\Generator\Columns\FloatColumn;
use KitLoong\MigrationsGenerator\Migration\Generator\Columns\IntegerColumn;
use KitLoong\MigrationsGenerator\Migration\Generator\Columns\MiscColumn;
use KitLoong\MigrationsGenerator\Migration\Generator\Columns\OmitNameColumn;
use KitLoong\MigrationsGenerator\Migration\Generator\Columns\PresetValuesColumn;
use KitLoong\MigrationsGenerator\Migration\Generator\Columns\SoftDeleteColumn;
use KitLoong\MigrationsGenerator\Migration\Generator\Columns\StringColumn;
use KitLoong\MigrationsGenerator\Migration\Migrator\Migrator;
use KitLoong\MigrationsGenerator\Repositories\MariaDBRepository;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Repositories\SQLiteRepository;
use KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository;
use KitLoong\MigrationsGenerator\Schema\MySQLSchema;
use KitLoong\MigrationsGenerator\Schema\PgSQLSchema;
use KitLoong\MigrationsGenerator\Schema\SQLiteSchema;
use KitLoong\MigrationsGenerator\Schema\SQLSrvSchema;

class MigrationsGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerConfig();

        // All the container singletons that should be registered.
        // Use $this->app->singleton instead of $singletons property to support lumen.
        foreach (
            [
                Setting::class           => Setting::class,
                MySQLRepository::class   => MySQLRepository::class,
                PgSQLRepository::class   => PgSQLRepository::class,
                SQLiteRepository::class  => SQLiteRepository::class,
                SQLSrvRepository::class  => SQLSrvRepository::class,
                MariaDBRepository::class => MariaDBRepository::class,
            ] as $abstract => $concrete
        ) {
            $this->app->singleton($abstract, $concrete);
        }

        foreach (
            [
                MySQLSchema::class  => DBALMySQLSchema::class,
                PgSQLSchema::class  => DBALPgSQLSchema::class,
                SQLiteSchema::class => DBALSQLiteSchema::class,
                SQLSrvSchema::class => DBALSQLSrvSchema::class,
            ] as $abstract => $concrete
        ) {
            $this->app->bind($abstract, $concrete);
        }

        // Bind the Repository Interface to $app['migrations.repository']
        $this->app->singleton(
            MigrationRepositoryInterface::class,
            function ($app) {
                return $app['migration.repository'];
            }
        );

        // Backward compatible for older Laravel version which failed to resolve Illuminate\Database\ConnectionResolverInterface.
        $this->app->singleton(
            Migrator::class,
            function ($app) {
                $repository = $app['migration.repository'];

                return new Migrator($repository, $app['db'], $app['files'], $app['events']);
            }
        );

        $this->registerColumnTypeGenerator();
    }

    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            MigrateGenerateCommand::class,
        ]);
    }

    /**
     * Register the config path.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/migrations-generator.php', 'migrations-generator');
    }

    /**
     * Make column generator singleton by type.
     *
     * @param  class-string<\KitLoong\MigrationsGenerator\Migration\Generator\Columns\ColumnTypeGenerator>  $columnTypeGenerator
     */
    protected function columnTypeSingleton(ColumnType $type, string $columnTypeGenerator): void
    {
        $this->app->singleton(ColumnType::class . '\\' . $type->getKey(), $columnTypeGenerator);
    }

    /**
     * Register column type generators.
     */
    protected function registerColumnTypeGenerator(): void
    {
        foreach (ColumnType::values() as $columnType) {
            $this->columnTypeSingleton($columnType, MiscColumn::class);
        }

        foreach (
            [
                ColumnType::BIG_INTEGER(),
                ColumnType::INTEGER(),
                ColumnType::MEDIUM_INTEGER(),
                ColumnType::SMALL_INTEGER(),
                ColumnType::TINY_INTEGER(),
            ] as $columnType
        ) {
            $this->columnTypeSingleton($columnType, IntegerColumn::class);
        }

        foreach (
            [
                ColumnType::DATE(),
                ColumnType::DATETIME(),
                ColumnType::DATETIME_TZ(),
                ColumnType::TIME(),
                ColumnType::TIME_TZ(),
                ColumnType::TIMESTAMP(),
                ColumnType::TIMESTAMP_TZ(),
            ] as $columnType
        ) {
            $this->columnTypeSingleton($columnType, DatetimeColumn::class);
        }

        foreach (
            [
                ColumnType::SOFT_DELETES(),
                ColumnType::SOFT_DELETES_TZ(),
            ] as $columnType
        ) {
            $this->columnTypeSingleton($columnType, SoftDeleteColumn::class);
        }

        foreach (
            [
                ColumnType::DECIMAL(),
                ColumnType::UNSIGNED_DECIMAL(),
            ] as $columnType
        ) {
            $this->columnTypeSingleton($columnType, DecimalColumn::class);
        }

        foreach (
            [
                ColumnType::ENUM(),
                ColumnType::SET(),
            ] as $columnType
        ) {
            $this->columnTypeSingleton($columnType, PresetValuesColumn::class);
        }

        foreach (
            [
                ColumnType::CHAR(),
                ColumnType::STRING(),
            ] as $columnType
        ) {
            $this->columnTypeSingleton($columnType, StringColumn::class);
        }

        $this->columnTypeSingleton(ColumnType::BOOLEAN(), BooleanColumn::class);
        $this->columnTypeSingleton(ColumnType::DOUBLE(), DoubleColumn::class);
        $this->columnTypeSingleton(ColumnType::FLOAT(), FloatColumn::class);
        $this->columnTypeSingleton(ColumnType::REMEMBER_TOKEN(), OmitNameColumn::class);
    }
}
