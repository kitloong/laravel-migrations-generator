<?php

namespace KitLoong\MigrationsGenerator\Tests;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\MigrationsGeneratorServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase as Testbench;
use PHPUnit\Framework\Constraint\IsEqual;

abstract class TestCase extends Testbench
{
    /**
     * @param  string  $name
     * @param  array  $arguments
     * @return object
     * @throws \Exception
     */
    public function __call(string $name, array $arguments)
    {
        switch ($name) {
            case 'mock':
                return $this->instance($arguments[0], Mockery::mock(...array_filter($arguments)));
            case 'partialMock':
                return $this->instance($arguments[0], Mockery::mock(...array_filter($arguments))->makePartial());
            default:
                throw new Exception('Call to undefined method ' . get_called_class() . '::' . $name . '()');
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            MigrationsGeneratorServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        app()->setBasePath(__DIR__ . '/../');
    }

    /**
     * Asserts that the contents of one file is equal to the contents of another file, ignore the ordering.
     *
     *
     * @param  string  $expected
     * @param  string  $actual
     * @param  string  $message
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
