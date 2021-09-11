<?php

namespace Tests\KitLoong;

use Exception;
use Illuminate\Support\Facades\File;
use KitLoong\MigrationsGenerator\MigrationsGeneratorServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase as Testbench;
use PHPUnit\Framework\Constraint\IsEqual;

abstract class TestCase extends Testbench
{
    /**
     * @param  string  $name
     * @param  array  $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call(string $name, array $arguments)
    {
        switch ($name) {
            case 'mock':
                return $this->instance($arguments[0], Mockery::mock(...array_filter($arguments)));
            case 'partialMock':
                return $this->instance($arguments[0], Mockery::mock(...array_filter($arguments))->makePartial());
            default:
                throw new Exception('Call to undefined method '.get_called_class().'::'.$name.'()');
        }
    }

    protected function getPackageProviders($app)
    {
        return [MigrationsGeneratorServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        app()->setBasePath(__DIR__.'/../../');

        $app['config']->set(
            'generators.config.migration_template_path',
            base_path('src/KitLoong/MigrationsGenerator/stub/migration.stub')
        );

//        $app['config']->set('generators.config.migration_target_path', $this->storageMigrations());
    }

    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file.
     *
     * @param  string  $expected
     * @param  string  $actual
     * @param  string  $message
     */
    public static function assertFileEqualsIgnoringOrder(string $expected, string $actual, string $message = ''): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        $expectedContent = file($expected);
        sort($expectedContent);

        $constraint = new IsEqual($expectedContent);

        $actualContent = file($actual);
        sort($actualContent);

        static::assertThat($actualContent, $constraint, $message);
    }

    protected function prepareStorage()
    {
        File::deleteDirectory(storage_path());
        File::makeDirectory($this->storageMigrations(), 0775, true);
        File::makeDirectory($this->storageFrom());
        File::makeDirectory($this->storageSql());
    }
}
