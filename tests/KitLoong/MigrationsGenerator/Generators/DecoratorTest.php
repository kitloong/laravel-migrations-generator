<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use KitLoong\MigrationsGenerator\Generators\Decorator;
use Tests\KitLoong\TestCase;

class DecoratorTest extends TestCase
{
    public function testColumnDefaultToString()
    {
        /** @var Decorator $decorator */
        $decorator = resolve(Decorator::class);
        $result = $decorator->columnDefaultToString('string with " !@#$%^^&*()_+ \' quotes');
        $this->assertSame('\'string with \" !@#$%^^&*()_+ \\\\\\\\\\\' quotes\'', $result);
    }

    public function testDecorate()
    {
        /** @var Decorator $decorator */
        $decorator = resolve(Decorator::class);
        $result = $decorator->decorate('method', []);
        $this->assertSame('method', $result);

        $result = $decorator->decorate('method', ['arg1', 'arg2']);
        $this->assertSame('method(arg1, arg2)', $result);
    }

    public function testAddSlash()
    {
        /** @var Decorator $decorator */
        $decorator = resolve(Decorator::class);
        $result = $decorator->addSlash("Content's");
        $this->assertSame("Content\'s", $result);
    }
}
