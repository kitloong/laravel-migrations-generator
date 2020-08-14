<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/07
 */

namespace KitLoong\MigrationsGenerator\Repositories;

use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class MySQLRepository
{
    public function getEnumPresetValues(string $table, string $columnName): ?string
    {
        /** @var MigrationsGeneratorSetting $setting */
        $setting = app(MigrationsGeneratorSetting::class);

        $column = $setting->getConnection()->select("SHOW COLUMNS FROM `${table}` where Field = '${columnName}' AND Type LIKE 'enum(%'");
        if (count($column) > 0) {
            return substr(
                str_replace('enum(', '[', $this->spaceAfterComma($column[0]->Type)),
                0,
                -1
            ).']';
        }
        return null;
    }

    public function getSetPresetValues(string $table, string $columnName): ?string
    {
        /** @var MigrationsGeneratorSetting $setting */
        $setting = app(MigrationsGeneratorSetting::class);

        $column = $setting->getConnection()->select("SHOW COLUMNS FROM `${table}` where Field = '${columnName}' AND Type LIKE 'set(%'");
        if (count($column) > 0) {
            return substr(
                str_replace('set(', '[', $this->spaceAfterComma($column[0]->Type)),
                0,
                -1
            ).']';
        }

        return null;
    }

    private function spaceAfterComma(string $value): string
    {
        return str_replace("','", "', '", $value);
    }
}
