<?php

namespace KitLoong\MigrationsGenerator\Tests;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\MigrationsGeneratorServiceProvider;
use Orchestra\Testbench\TestCase as Testbench;
use PHPUnit\Framework\Constraint\IsEqual;

abstract class TestCase extends Testbench
{
    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app)
    {
        return [
            MigrationsGeneratorServiceProvider::class,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        // Tests run with @runTestsInSeparateProcesses failed since https://github.com/sebastianbergmann/phpunit/commit/3291172e198f044a922af8036378719f71267a51 and https://github.com/php/php-src/pull/11169.
        // The root caused is not determined yet, however, by revert `set_error_handler` (https://github.com/laravel/framework/blob/2967d89906708cc7d619fc130e835c8002b7d3e3/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php#L45C20-L45C20) to default seems fix the failed test for now.
        restore_error_handler();

        app()->setBasePath(__DIR__ . '/../');
    }

    /**
     * Asserts that the contents of one file is equal to the contents of another file, ignore the ordering.
     * Also, strip all end of line commas.
     */
    public static function assertFileEqualsIgnoringOrder(string $expected, string $actual, string $message = ''): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        $removeLastComma = static fn (string $line): string => Str::endsWith($line, ',' . PHP_EOL)
                ? Str::replaceLast(',' . PHP_EOL, PHP_EOL, $line)
                : $line;

        $expectedFiles   = file($expected) ?: [];
        $expectedContent = new Collection($expectedFiles);
        $expectedContent = $expectedContent->map($removeLastComma)->sort();

        $constraint = new IsEqual($expectedContent->values());

        $actualFiles   = file($actual) ?: [];
        $actualContent = new Collection($actualFiles);
        $actualContent = $actualContent->map($removeLastComma)->sort();

        static::assertThat($actualContent->values(), $constraint, $message);
    }
}
