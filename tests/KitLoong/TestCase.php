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

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function packageBasePath(string $path): string
    {
        return __DIR__.'/../../'.$path;
    }
}
