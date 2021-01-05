<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/11/15
 */

namespace Tests\KitLoong\Feature;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use KitLoong\MigrationsGenerator\MigrationsGeneratorServiceProvider;
use Tests\KitLoong\TestCase;

abstract class FeatureTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [MigrationsGeneratorServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        try {
            $this->loadDotenv();
        } catch (InvalidPathException $exception) {
            $this->markTestSkipped('Skipped feature tests.');
        }

        $app['config']->set(
            'generators.config.migration_template_path',
            base_path('src/Way/Generators/templates/migration.txt')
        );

        $app['config']->set('generators.config.migration_target_path', $this->migrationOutputPath());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareStorage();
        $this->dropAllTables();
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
        File::makeDirectory(config('generators.config.migration_target_path'), 0775, true);
        File::makeDirectory($this->migrateFromPath());
        File::makeDirectory($this->sqlOutputPath());
    }

    protected function migrationOutputPath(string $path = ''): string
    {
        return storage_path('migrations').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    protected function migrateFromPath(string $path = ''): string
    {
        return storage_path('from').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    protected function sqlOutputPath(string $path = ''): string
    {
        return storage_path('sql').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    protected function migrateExpected(string $connection): void
    {
        File::copyDirectory(base_path('tests/KitLoong/resources/database/migrations'), $this->migrateFromPath());
        foreach (File::files($this->migrateFromPath()) as $file) {
            $content = str_replace([
                '[db]', '_DB_Table'
            ], [
                $connection, ucfirst("${connection}Table")
            ], $file->getContents());

            file_put_contents($this->migrateFromPath($file->getBasename()), $content);
            File::move(
                $this->migrateFromPath($file->getBasename()),
                $this->migrateFromPath(str_replace('_db_', "_${connection}_", $file->getBasename()))
            );
        }

        $this->loadMigrationsFrom($this->migrateFromPath());
    }

    protected function generateMigrations(): void
    {
        $this->artisan(
            'migrate:generate',
            ['--no-interaction' => true]
        );
    }

    protected function truncateMigration()
    {
        DB::table('migrations')->truncate();
    }

    abstract protected function dropAllTables(): void;
}
