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
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function register()
    {
        $this->registerConfig();

        // All the container singletons that should be registered.
        // Use $this->app->singleton instead of $singletons property to support lumen.
        foreach (
            [
                Setting::class           => Setting::class,
                MySQLRepository::class   => MySQLRepository::class,
                MySQLSchema::class       => DBALMySQLSchema::class,
                PgSQLRepository::class   => PgSQLRepository::class,
                PgSQLSchema::class       => DBALPgSQLSchema::class,
                SQLiteRepository::class  => SQLiteRepository::class,
                SQLiteSchema::class      => DBALSQLiteSchema::class,
                SQLSrvRepository::class  => SQLSrvRepository::class,
                SQLSrvSchema::class      => DBALSQLSrvSchema::class,
                MariaDBRepository::class => MariaDBRepository::class,
            ] as $abstract => $concrete
        ) {
            $this->app->singleton($abstract, $concrete);
        }

        // Bind the Repository Interface to $app['migrations.repository']
        $this->app->bind(
            MigrationRepositoryInterface::class,
            function ($app) {
                return $app['migration.repository'];
            }
        );

        $this->registerColumnTypeGenerator();
    }

    public function boot()
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
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function registerConfig()
    {
        $packageConfigFile = __DIR__ . '/../config/config.php';
        $this->app->make('config')->set(
            'generators.config',
            $this->app->make('files')->getRequire($packageConfigFile)
        );
    }

    /**
     * Make column generator singleton by type.
     *
     * @param  \KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType  $type
     * @param  string  $columnTypeGenerator
     */
    protected function columnTypeSingleton(ColumnType $type, string $columnTypeGenerator): void
    {
        $this->app->singleton(ColumnType::class . '\\' . $type->getKey(), $columnTypeGenerator);
    }

    /**
     * Register column type generators.
     *
     * @return void
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
