<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators\Modifier;

use KitLoong\MigrationsGenerator\Generators\Modifier\NullableModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use Tests\KitLoong\TestCase;

class NullableModifierTest extends TestCase
{
    public function testShouldAddNullableModifier()
    {
        /** @var NullableModifier $nullableModifier */
        $nullableModifier = resolve(NullableModifier::class);

        $this->assertFalse($nullableModifier->shouldAddNullableModifierOld(ColumnType::SOFT_DELETES));
        $this->assertFalse($nullableModifier->shouldAddNullableModifierOld(ColumnType::REMEMBER_TOKEN));
        $this->assertFalse($nullableModifier->shouldAddNullableModifierOld(ColumnType::TIMESTAMPS));

        $this->assertTrue($nullableModifier->shouldAddNullableModifierOld('others'));
    }
}
