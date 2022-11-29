<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature;

use Doctrine\DBAL\Schema\View;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use KitLoong\MigrationsGenerator\Support\AssetNameQuote;
use KitLoong\MigrationsGenerator\Tests\TestCase;

abstract class FeatureTestCase extends TestCase
{
    use AssetNameQuote;

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

        $this->prepareStoragePath();
    }

    protected function tearDown(): void
    {
        $this->refreshDatabase();

        parent::tearDown();
    }

    protected function loadDotenv()
    {
        if (method_exists(Dotenv::class, 'createImmutable')) {
            $dotenv = Dotenv::createImmutable(base_path());
            $dotenv->load();
            return;
        }

        if (method_exists(Dotenv::class, 'create')) {
            /** @noinspection PhpParamsInspection */
            $dotenv = Dotenv::create(base_path());
            $dotenv->load();
            return;
        }

        /** @noinspection PhpParamsInspection */
        $dotenv = new Dotenv(base_path());
        $dotenv->load();
    }

    protected function prepareStoragePath(): void
    {
        File::deleteDirectory(storage_path());
        File::makeDirectory($this->getStorageMigrationsPath(), 0775, true);
        File::makeDirectory($this->getStorageFromPath());
        File::makeDirectory($this->getStorageSqlPath());
    }

    protected function getStorageMigrationsPath(string $path = ''): string
    {
        return storage_path('migrations') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    protected function getStorageFromPath(string $path = ''): string
    {
        return storage_path('from') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    protected function getStorageSqlPath(string $path = ''): string
    {
        return storage_path('sql') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    protected function migrateGeneral(string $connection): void
    {
        $this->migrateFromTemplate($connection, base_path('tests/resources/database/migrations/general'));
    }

    protected function migrateCollation(string $connection): void
    {
        $this->migrateFromTemplate($connection, base_path('tests/resources/database/migrations/collation'));
    }

    protected function migrateFromTemplate(string $connection, string $templatePath): void
    {
        File::copyDirectory($templatePath, $this->getStorageFromPath());

        foreach (File::files($this->getStorageFromPath()) as $file) {
            $content = str_replace([
                '[db]',
                '_DB_',
            ], [
                $connection,
                ucfirst("$connection"),
            ], $file->getContents());

            File::put($this->getStorageFromPath($file->getBasename()), $content);
            File::move(
                $this->getStorageFromPath($file->getBasename()),
                $this->getStorageFromPath(str_replace('_db_', "_${connection}_", $file->getBasename()))
            );
        }

        $this->runMigrationsFrom($connection, $this->getStorageFromPath());
    }

    protected function runMigrationsFrom(string $connection, string $path): void
    {
        $this->artisan('migrate', [
            '--database' => $connection,
            '--realpath' => true,
            '--path'     => $path,
        ]);
    }

    protected function rollbackMigrationsFrom(string $connection, string $path): void
    {
        $this->artisan('migrate:rollback', [
            '--database' => $connection,
            '--realpath' => true,
            '--path'     => $path,
        ]);
    }

    /**
     * Generate migration files to $this->storageMigrations()
     *
     * @see \KitLoong\MigrationsGenerator\Tests\Feature\FeatureTestCase::getEnvironmentSetUp()
     */
    protected function generateMigrations(array $options = []): void
    {
        $this->artisan(
            'migrate:generate',
            array_merge([
                '--path' => $this->getStorageMigrationsPath(),
            ], $options)
        )
            ->expectsQuestion('Do you want to log these migrations in the migrations table?', true)
            ->expectsQuestion(
                'Next Batch Number is: 1. We recommend using Batch Number 0 so that it becomes the "first" migration. [Default: 0]',
                '0'
            );
    }

    protected function assertMigrations(): void
    {
        $migrations = [];

        foreach (File::files($this->getStorageMigrationsPath()) as $migration) {
            $migrations[] = $migration->getFilenameWithoutExtension();
        }

        $dbMigrations = app(MigrationRepositoryInterface::class)->getRan();

        // Both file and DB migrations are sorted by name ascending however the result is slightly different.
        // Use PHP sort here to maintain same ordering.
        sort($migrations);
        sort($dbMigrations);

        $this->assertSame($migrations, $dbMigrations);
    }

    protected function truncateMigrationsTable()
    {
        DB::table('migrations')->truncate();
    }

    /**
     * Get a list of table names.
     *
     * @return string[]
     */
    protected function getTableNames(): array
    {
        return collect(DB::getDoctrineSchemaManager()->listTableNames())
            ->map(function ($table) {
                // The table name may contain quotes.
                // Always trim quotes before set into list.
                if ($this->isIdentifierQuoted($table)) {
                    return $this->trimQuotes($table);
                }

                return $table;
            })
            ->toArray();
    }

    /**
     * Get a list of view names.
     *
     * @return string[]
     */
    protected function getViewNames(): array
    {
        return collect(DB::getDoctrineSchemaManager()->listViews())
            ->map(function (View $view) {
                return $view->getName();
            })
            ->toArray();
    }

    abstract protected function refreshDatabase(): void;
}
