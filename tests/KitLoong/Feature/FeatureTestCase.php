<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/11/15
 */

namespace Tests\KitLoong\Feature;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
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
        $dotenv = Dotenv::createImmutable(base_path());
        $dotenv->load();
    }

    protected function prepareStorage()
    {
        File::deleteDirectory(storage_path());
        File::makeDirectory(config('generators.config.migration_target_path'), 0775, true);
        File::makeDirectory($this->sqlOutputPath());
    }

    protected function migrationOutputPath(string $path = ''): string
    {
        return storage_path('migrations').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    protected function sqlOutputPath(string $path = ''): string
    {
        return storage_path('sql').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    protected function generateMigrations(): void
    {
        $this->artisan(
            'migrate:generate',
            ['--no-interaction' => true]
        )->run();
    }

    abstract protected function dropAllTables(): void;
}
