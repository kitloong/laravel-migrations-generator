<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/07
 */

namespace KitLoong\MigrationsGenerator\Repositories;

use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class MySQLRepository extends Repository
{
    /**
     * @return array [
     *  'charset' => string,
     *  'collation' => string
     * ]
     */
    public function getDatabaseCollation(): array
    {
        $setting = app(MigrationsGeneratorSetting::class);
        $columns = $setting->getConnection()->select("SELECT @@character_set_database, @@collation_database");
        return ['charset' => $columns[0]->{'@@character_set_database'}, 'collation' => $columns[0]->{'@@collation_database'}];
    }

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

    public function useOnUpdateCurrentTimestamp(string $table, string $columnName): bool
    {
        $setting = app(MigrationsGeneratorSetting::class);

        // MySQL5.7 shows on update CURRENT_TIMESTAMP
        // MySQL8 shows DEFAULT_GENERATED on update CURRENT_TIMESTAMP
        $column = $setting->getConnection()
            ->select(
                "SHOW COLUMNS FROM `${table}`
                WHERE Field = '${columnName}'
                    AND Type = 'timestamp'
                    AND EXTRA LIKE '%on update CURRENT_TIMESTAMP%'"
            );
        if (count($column) > 0) {
            return true;
        }

        return false;
    }

    private function spaceAfterComma(string $value): string
    {
        return str_replace("','", "', '", $value);
    }
}
