<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 * Time: 14:58
 */

namespace KitLoong\MigrationsGenerator\Generators;

class OtherField
{
    public function makeField(array $field)
    {
        if (isset(FieldGenerator::$fieldTypeMap[$field['type']])) {
            $field['type'] = FieldGenerator::$fieldTypeMap[$field['type']];
        }
        return $field;
    }
}
