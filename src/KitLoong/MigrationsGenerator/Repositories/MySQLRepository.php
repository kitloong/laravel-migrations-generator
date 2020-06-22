<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/07
 */

namespace KitLoong\MigrationsGenerator\Repositories;

use KitLoong\MigrationsGenerator\MigrationGeneratorSetting;

class MySQLRepository
{
    public function getEnumPresetValues(string $table, string $columnName): ?string
    {
        /** @var MigrationGeneratorSetting $setting */
        $setting = app(MigrationGeneratorSetting::class);

        $column = $setting->getConnection()->select("SHOW COLUMNS FROM `${table}` where Field = '${columnName}' AND Type LIKE 'enum(%'");
        if (count($column) > 0) {
            return substr(
                str_replace('enum(', '[', $column[0]->Type),
                0,
                -1
            ).']';
        }
        return null;
    }

    public function getSetPresetValues(string $table, string $columnName): ?string
    {
        /** @var MigrationGeneratorSetting $setting */
        $setting = app(MigrationGeneratorSetting::class);

        $column = $setting->getConnection()->select("SHOW COLUMNS FROM `${table}` where Field = '${columnName}' AND Type LIKE 'set(%'");
        if (count($column) > 0) {
            return substr(
                str_replace('set(', '[', $column[0]->Type),
                0,
                -1
            ).']';
        }

        return null;
    }
}
