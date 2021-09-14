<?php

namespace Tests\KitLoong\Feature;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\KitLoong\TestCase;

abstract class FeatureTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        try {
            $this->loadDotenv();
        } catch (InvalidPathException $exception) {
            $this->markTestSkipped('Skipped feature tests.');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareStorage();
        $this->dropAllTables();
    }

    protected function tearDown(): void
    {
        $this->dropAllTables();

        parent::tearDown();
    }

    protected function loadDotenv()
    {
        if (method_exists(Dotenv::class, 'createImmutable')) {
            $dotenv = Dotenv::createImmutable(base_path());
        } elseif (method_exists(Dotenv::class, 'create')) {
            /** @noinspection PhpParamsInspection */
            $dotenv = Dotenv::create(base_path());
        } else {
            /** @noinspection PhpParamsInspection */
            $dotenv = new Dotenv(base_path());
        }
        $dotenv->load();
    }

    protected function prepareStorage()
    {
        File::deleteDirectory(storage_path());
        File::makeDirectory($this->storageMigrations(), 0775, true);
        File::makeDirectory($this->storageFrom());
        File::makeDirectory($this->storageSql());
    }

    protected function storageMigrations(string $path = ''): string
    {
        return storage_path('migrations').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    protected function storageFrom(string $path = ''): string
    {
        return storage_path('from').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    protected function storageSql(string $path = ''): string
    {
        return storage_path('sql').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    protected function migrateGeneral(string $connection): void
    {
        $this->migrateFromTemplate($connection, base_path('tests/KitLoong/resources/database/migrations/general'));
    }

    protected function migrateCollation(string $connection): void
    {
        $this->migrateFromTemplate($connection, base_path('tests/KitLoong/resources/database/migrations/collation'));
    }

    protected function migrateFromTemplate(string $connection, string $templatePath): void
    {
        File::copyDirectory($templatePath, $this->storageFrom());
        foreach (File::files($this->storageFrom()) as $file) {
            $content = str_replace([
                '[db]', '_DB_Table'
            ], [
                $connection, ucfirst("${connection}Table")
            ], $file->getContents());

            file_put_contents($this->storageFrom($file->getBasename()), $content);
            File::move(
                $this->storageFrom($file->getBasename()),
                $this->storageFrom(str_replace('_db_', "_${connection}_", $file->getBasename()))
            );
        }

        $this->runMigrationsFrom($this->storageFrom());
    }

    protected function runMigrationsFrom(string $path): void
    {
        $this->artisan('migrate', [
            '--realpath' => true,
            '--path' => $path
        ]);
    }

    protected function rollbackMigrationsFrom($path): void
    {
        $this->artisan('migrate:rollback', [
            '--realpath' => true,
            '--path' => $path
        ]);
    }

    /**
     * Generate migration files to $this->storageMigrations()
     * @see \Tests\KitLoong\Feature\FeatureTestCase::getEnvironmentSetUp()
     */
    protected function generateMigrations(array $options = []): void
    {
        $this->artisan(
            'migrate:generate',
            array_merge($options, [
                '--path' => $this->storageMigrations(),
                '--templatePath' => base_path('src/KitLoong/MigrationsGenerator/stub/migration.stub'),
            ])
        )
            ->expectsQuestion('Do you want to log these migrations in the migrations table? [Y/n] ', 'y')
            ->expectsQuestion('Next Batch Number is: 1. We recommend using Batch Number 0 so that it becomes the "first" migration [Default: 0] ', 0);
    }

    protected function assertMigrations(): void
    {
        $migrations = [];
        foreach (File::files($this->storageMigrations()) as $migration) {
            $migrations[] = $migration->getFilenameWithoutExtension();
        }

        $dbMigrations = app(MigrationRepositoryInterface::class)->getRan();

        // Both file and DB migrations are sorted by name ascending however the result is slightly different.
        // Use PHP sort here to maintain same ordering.
        sort($migrations);
        sort($dbMigrations);

        $this->assertSame($migrations, $dbMigrations);
    }

    protected function truncateMigration()
    {
        DB::table('migrations')->truncate();
    }

    abstract protected function dropAllTables(): void;
}
