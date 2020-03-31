<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 * Time: 16:26
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Illuminate\Support\Facades\DB;

class SetField
{
    private $decorator;

    public function __construct(Decorator $decorator)
    {
        $this->decorator = $decorator;
    }

    public function makeField(string $tableName, array $field): array
    {
        $column = DB::connection(resolve('connection')->getConnection())->select("SHOW COLUMNS FROM `${tableName}` where Field = '${field['field']}' AND Type LIKE 'set(%'");
        if (count($column) > 0) {
            $field['args'][] = substr(
                str_replace('set(', '[', $column[0]->Type),
                0,
                -1
            ).']';
        }
        return $field;
    }
}
