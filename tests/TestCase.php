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

        $removeLastComma = function (string $line): string {
            return Str::endsWith($line, ',' . PHP_EOL)
                ? Str::replaceLast(',' . PHP_EOL, PHP_EOL, $line)
                : $line;
        };

        $expectedContent = new Collection(file($expected));
        $expectedContent = $expectedContent->map($removeLastComma)->sort();

        $constraint = new IsEqual($expectedContent->values());

        $actualContent = new Collection(file($actual));
        $actualContent = $actualContent->map($removeLastComma)->sort();

        static::assertThat($actualContent->values(), $constraint, $message);
    }
}
