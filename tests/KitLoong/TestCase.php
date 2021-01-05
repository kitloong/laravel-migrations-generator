<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/12/29
 */

namespace Tests\KitLoong;

use Exception;
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

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        app()->setBasePath(__DIR__.'/../../');
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
}
