<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use KitLoong\MigrationsGenerator\Generators\OtherField;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use Tests\KitLoong\TestCase;

class OtherFieldTest extends TestCase
{
    public function testMakeField()
    {
        /** @var OtherField $otherField */
        $otherField = resolve(OtherField::class);

        $field = [
            'field' => 'field',
            'type' => 'blob'
        ];

        $field = $otherField->makeField($field);
        $this->assertSame(ColumnType::BINARY, $field['type']);
    }
}
