<?php namespace Xethron\MigrationsGenerator;

use Mockery;
use Orchestra\Testbench\TestCase;

class MigrationsGeneratorTest extends TestCase
{

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testRegistersMigrationsGenerator()
    {
        //
    }
}
