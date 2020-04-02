<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 * Time: 15:13
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\MigrationGeneratorSetting;

class EnumField
{
    private $decorator;

    public function __construct(Decorator $decorator)
    {
        $this->decorator = $decorator;
    }

    public function makeField(string $tableName, array $field): array
    {
        /** @var MigrationGeneratorSetting $setting */
        $setting = app(MigrationGeneratorSetting::class);

        $column = DB::connection($setting->getConnection())->select("SHOW COLUMNS FROM `${tableName}` where Field = '${field['field']}' AND Type LIKE 'enum(%'");
        if (count($column) > 0) {
            $field['args'][] = substr(
                str_replace('enum(', '[', $column[0]->Type),
                0,
                -1
            ).']';
        }
        return $field;
    }
}
