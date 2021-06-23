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

//        $app['config']->set('generators.config.migration_target_path', $this->storageMigrations());
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

    private function migrateFromTemplate(string $connection, string $templatePath): void
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

        $this->loadMigrationsFrom($this->storageFrom());
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
                '--no-interaction' => true,
            ])
        );
    }

    protected function truncateMigration()
    {
        DB::table('migrations')->truncate();
    }

    abstract protected function dropAllTables(): void;
}
