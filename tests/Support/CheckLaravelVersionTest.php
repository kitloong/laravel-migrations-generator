<?php

namespace Tests\Support;

use Illuminate\Support\Facades\App;
use MigrationsGenerator\Support\CheckLaravelVersion;
use Tests\TestCase;

class CheckLaravelVersionTest extends TestCase
{
    public function testAtLeastLaravel5Dot7()
    {
        App::shouldReceive('version')->andReturn('5.6.0')->once();
        $this->assertFalse($this->stubInstance()->atLeastLaravel5Dot7());

        App::shouldReceive('version')->andReturn('5.7.0')->once();
        $this->assertTrue($this->stubInstance()->atLeastLaravel5Dot7());

        App::shouldReceive('version')->andReturn('5.8.0')->once();
        $this->assertTrue($this->stubInstance()->atLeastLaravel5Dot7());
    }

    public function testAtLeastLaravel5Dot8()
    {
        App::shouldReceive('version')->andReturn('5.7.0')->once();
        $this->assertFalse($this->stubInstance()->atLeastLaravel5Dot8());

        App::shouldReceive('version')->andReturn('5.8.0')->once();
        $this->assertTrue($this->stubInstance()->atLeastLaravel5Dot8());

        App::shouldReceive('version')->andReturn('6.0.0')->once();
        $this->assertTrue($this->stubInstance()->atLeastLaravel5Dot8());
    }

    public function testAtLeastLaravel6()
    {
        App::shouldReceive('version')->andReturn('5.8.0')->once();
        $this->assertFalse($this->stubInstance()->atLeastLaravel6());

        App::shouldReceive('version')->andReturn('6.0.0')->once();
        $this->assertTrue($this->stubInstance()->atLeastLaravel6());

        App::shouldReceive('version')->andReturn('7.0.0')->once();
        $this->assertTrue($this->stubInstance()->atLeastLaravel6());
    }

    public function testAtLeastLaravel7()
    {
        App::shouldReceive('version')->andReturn('6.0.0')->once();
        $this->assertFalse($this->stubInstance()->atLeastLaravel7());

        App::shouldReceive('version')->andReturn('7.0.0')->once();
        $this->assertTrue($this->stubInstance()->atLeastLaravel7());

        App::shouldReceive('version')->andReturn('8.0.0')->once();
        $this->assertTrue($this->stubInstance()->atLeastLaravel7());
    }

    public function testAtLeastLaravel8()
    {
        App::shouldReceive('version')->andReturn('7.0.0')->once();
        $this->assertFalse($this->stubInstance()->atLeastLaravel8());

        App::shouldReceive('version')->andReturn('8.0.0')->once();
        $this->assertTrue($this->stubInstance()->atLeastLaravel8());

        App::shouldReceive('version')->andReturn('9.0.0')->once();
        $this->assertTrue($this->stubInstance()->atLeastLaravel8());
    }

    private function stubInstance()
    {
        return new class() {
            use CheckLaravelVersion;
        };
    }
}
