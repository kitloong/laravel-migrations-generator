<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators\Modifier;

use KitLoong\MigrationsGenerator\Generators\Modifier\CommentModifier;
use Orchestra\Testbench\TestCase;

class CommentModifierTest extends TestCase
{
    public function testGenerate()
    {
        /** @var CommentModifier $commentModifier */
        $commentModifier = resolve(CommentModifier::class);

        $result = $commentModifier->generate('comment with \'" quotes');
        $this->assertSame('comment(\'comment with \\\'" quotes\')', $result);
    }
}
