<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 19:12
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators\Modifier;

use KitLoong\MigrationsGenerator\Generators\Modifier\IndexModifier;
use Orchestra\Testbench\TestCase;

class IndexModifierTest extends TestCase
{
    public function testGenerate()
    {
        /** @var IndexModifier $indexModifier */
        $indexModifier = resolve(IndexModifier::class);

        $index = [
            'type' => 'index',
            'args' => ['index_name']
        ];
        $result = $indexModifier->generate($index);
        $this->assertSame('index(index_name)', $result);
    }

    public function testGenerateIfIndexIsNull()
    {
        /** @var IndexModifier $indexModifier */
        $indexModifier = resolve(IndexModifier::class);

        $index = [
            'type' => 'index',
            'args' => []
        ];
        $result = $indexModifier->generate($index);
        $this->assertSame('index', $result);
    }
}
